<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\TreatmentPatientPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorPatientRecordController extends Controller
{
    public function index(Request $request): View
    {
        $doctorId = auth('doctor')->id();
        $search = trim($request->string('search')->toString());

        $appointments = Appointment::query()
            ->with(['patient:id,name,email,phone,status'])
            ->where('doctor_id', $doctorId)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        $records = $appointments
            ->filter(fn ($appointment) => filled($appointment->patient_id) && $appointment->patient !== null)
            ->groupBy('patient_id')
            ->map(function ($rows) {
                $patient = $rows->first()->patient;
                $lastAppointment = $rows
                    ->sortByDesc(fn ($row) => ($row->appointment_date?->toDateString() ?? '').' '.(string) $row->appointment_time)
                    ->first();
                $nextAppointment = $rows
                    ->filter(fn ($row) => ($row->appointment_date?->toDateString() ?? '') >= now()->toDateString())
                    ->sortBy(fn ($row) => ($row->appointment_date?->toDateString() ?? '').' '.(string) $row->appointment_time)
                    ->first();

                return (object) [
                    'patient' => $patient,
                    'total_appointments' => $rows->count(),
                    'completed_appointments' => $rows->where('status', 'completed')->count(),
                    'cancelled_appointments' => $rows->where('status', 'cancelled')->count(),
                    'last_appointment' => $lastAppointment,
                    'next_appointment' => $nextAppointment,
                ];
            })
            ->values();

        $patientIds = $records->pluck('patient.id')->filter()->unique()->values();
        $today = now()->toDateString();

        $activeMembershipByPatient = PatientSubscription::query()
            ->with('membershipPlan:id,name')
            ->whereIn('patient_id', $patientIds)
            ->where('status', 'active')
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->orderByDesc('start_date')
            ->get()
            ->groupBy('patient_id')
            ->map(fn ($group) => $group->first());

        $activePackageByPatient = TreatmentPatientPackage::query()
            ->with('treatmentPackage:id,name')
            ->whereIn('patient_id', $patientIds)
            ->where('status', 'active')
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->orderByDesc('start_date')
            ->get()
            ->groupBy('patient_id')
            ->map(fn ($group) => $group->first());

        $records = $records->map(function ($record) use ($activeMembershipByPatient, $activePackageByPatient) {
            $patientId = $record->patient?->id;
            $activeMembership = $activeMembershipByPatient->get($patientId);
            $activePackage = $activePackageByPatient->get($patientId);

            if ($activeMembership?->membershipPlan?->name) {
                $record->active_plan = 'Membership: '.$activeMembership->membershipPlan->name;
            } elseif ($activePackage?->treatmentPackage?->name) {
                $record->active_plan = 'Package: '.$activePackage->treatmentPackage->name;
            } else {
                $record->active_plan = 'No active plan';
            }

            return $record;
        })->values();

        if ($search !== '') {
            $records = $records->filter(function ($record) use ($search) {
                $patient = $record->patient;
                $haystack = strtolower(implode(' ', [
                    (string) ($patient->name ?? ''),
                    (string) ($patient->email ?? ''),
                    (string) ($patient->phone ?? ''),
                ]));

                return str_contains($haystack, strtolower($search));
            })->values();
        }

        return view('doctor.patient-records.index', [
            'records' => $records,
            'search' => $search,
        ]);
    }

    public function show(Patient $patient): View
    {
        $doctorId = auth('doctor')->id();

        $appointments = Appointment::query()
            ->with(['service:id,name', 'doctor:id,name', 'note'])
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patient->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        abort_if($appointments->isEmpty(), 404);

        $lastVisit = $appointments->first();
        $totalVisits = $appointments->count();
        $latestNote = $appointments->first(fn ($appt) => $appt->note !== null)?->note;
        $latestAlerts = $appointments
            ->map(fn ($appt) => $appt->note?->alerts)
            ->filter()
            ->first();

        $upcomingAppointments = $appointments
            ->filter(fn ($appt) => ($appt->appointment_date?->toDateString() ?? '') >= now()->toDateString())
            ->values();
        $pastAppointments = $appointments
            ->filter(fn ($appt) => ($appt->appointment_date?->toDateString() ?? '') < now()->toDateString())
            ->values();

        $subscriptions = PatientSubscription::query()
            ->with('membershipPlan:id,name')
            ->where('patient_id', $patient->id)
            ->orderByDesc('start_date')
            ->get();

        $packages = TreatmentPatientPackage::query()
            ->with('treatmentPackage:id,name')
            ->where('patient_id', $patient->id)
            ->orderByDesc('start_date')
            ->get();

        $payments = Payment::query()
            ->where('patient_id', $patient->id)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $appointmentPayments = AppointmentPayment::query()
            ->whereHas('appointment', function ($query) use ($doctorId, $patient) {
                $query->where('doctor_id', $doctorId)
                    ->where('patient_id', $patient->id);
            })
            ->with('appointment:id,appointment_no,doctor_id,patient_id')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $notesHistory = $appointments
            ->filter(fn ($appt) => $appt->note !== null)
            ->map(function ($appt) {
                return (object) [
                    'appointment' => $appt,
                    'note' => $appt->note,
                ];
            })
            ->values();

        return view('doctor.appointments.patient-record.show', compact(
            'patient',
            'appointments',
            'lastVisit',
            'totalVisits',
            'latestNote',
            'latestAlerts',
            'upcomingAppointments',
            'pastAppointments',
            'subscriptions',
            'packages',
            'payments',
            'appointmentPayments',
            'notesHistory',
        ));
    }

    public function storeNote(Request $request, Patient $patient): RedirectResponse
    {
        $doctorId = auth('doctor')->id();

        $validated = $request->validate([
            'appointment_id' => ['required', 'integer'],
            'observation' => ['nullable', 'string', 'max:2000'],
            'procedure_done' => ['nullable', 'string', 'max:2000'],
            'recommendation' => ['nullable', 'string', 'max:2000'],
            'follow_up' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment = Appointment::query()
            ->where('id', $validated['appointment_id'])
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        $hasAny = collect([
            $validated['observation'] ?? null,
            $validated['procedure_done'] ?? null,
            $validated['recommendation'] ?? null,
            $validated['follow_up'] ?? null,
        ])->contains(fn ($value) => filled($value));

        if (! $hasAny) {
            return back()->withErrors(['observation' => 'Please fill at least one treatment note field.'])->withInput();
        }

        AppointmentNote::query()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'doctor_notes' => $validated['observation'] ?? null,
                'appointment_remarks' => $validated['procedure_done'] ?? null,
                'instructions' => $validated['recommendation'] ?? null,
                'alerts' => $validated['follow_up'] ?? null,
            ]
        );

        return redirect()
            ->route('doctor.patient-records.show', $patient)
            ->with('success', 'Treatment note saved successfully.');
    }
}
