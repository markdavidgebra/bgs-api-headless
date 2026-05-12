<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Notifications\Patient\AppointmentBookedPatientNotification;
use App\Notifications\Patient\AppointmentRescheduledPatientNotification;
use App\Support\DoctorAppointmentAlerts;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class PatientAppointmentController extends Controller
{
    public function index(): View
    {
        $patient = Auth::user();
        $patientId = $patient->id;

        $upcomingAppointments = Appointment::query()
            ->where('patient_id', $patientId)
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->with(['doctor', 'service'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $pastAppointments = Appointment::query()
            ->where('patient_id', $patientId)
            ->where(function ($q) {
                $q->whereDate('appointment_date', '<', now()->toDateString())
                    ->orWhereIn('status', ['completed', 'cancelled']);
            })
            ->with(['doctor', 'service'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(15)
            ->withQueryString();

        return view('patient.appointments.index', [
            'patient' => $patient,
            'upcomingAppointments' => $upcomingAppointments,
            'pastAppointments' => $pastAppointments,
        ]);
    }

    public function book(Request $request): View
    {
        $services = Service::query()
            ->where('status', 'active')
            ->where('is_bookable', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes', 'price']);

        $dateForDoctors = old('appointment_date') ?: $request->query('date');
        $serviceForDoctors = old('service_id') ?: $request->query('service_id');
        $doctors = $this->bookableDoctorsQuery($dateForDoctors, $serviceForDoctors ? (int) $serviceForDoctors : null)
            ->get(['id', 'name', 'specialty']);

        return view('patient.appointments.book', [
            'services' => $services,
            'doctors' => $doctors,
        ]);
    }

    public function doctorsForBookingDate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ]);

        $doctors = $this->bookableDoctorsQuery($data['date'], isset($data['service_id']) ? (int) $data['service_id'] : null)
            ->get(['id', 'name', 'specialty']);

        return response()->json(['doctors' => $doctors]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'patient_concern' => ['nullable', 'string', 'max:2000'],
        ]);

        $patientId = Auth::id();

        if (! $this->bookableDoctorsQuery($data['appointment_date'], (int) $data['service_id'])->whereKey((int) $data['doctor_id'])->exists()) {
            throw ValidationException::withMessages([
                'doctor_id' => 'This doctor is not available on the selected date for the selected service.',
            ]);
        }

        $appointment = DB::transaction(function () use ($data, $patientId) {
            $appointment = Appointment::create([
                'appointment_no' => $this->generateAppointmentNo(),
                'patient_id' => $patientId,
                'doctor_id' => (int) $data['doctor_id'],
                'service_id' => (int) $data['service_id'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'status' => 'pending',
            ]);

            if (! empty($data['patient_concern'])) {
                $patient = Patient::query()->find($patientId);
                AppointmentNote::create([
                    'appointment_id' => $appointment->id,
                    'patient_concern' => $data['patient_concern'],
                    'section_authors' => [
                        'patient_concern' => AppointmentNote::authorPayloadFromUserName(
                            'patient',
                            $patient?->name,
                        ),
                    ],
                ]);
            }

            return $appointment;
        });

        $appointment->load(['patient:id,name,email', 'doctor:id,name', 'service:id,name']);
        if ($appointment->patient && filled($appointment->patient->email)) {
            Notification::send($appointment->patient, new AppointmentBookedPatientNotification($appointment));
        }
        DoctorAppointmentAlerts::notifyDoctorOfNewBooking($appointment);

        return redirect()
            ->route('patient.appointments.show', $appointment)
            ->with('success', 'Appointment booked successfully. A booking confirmation email has been sent to your registered email.');
    }

    public function show(Appointment $appointment): View
    {
        $this->ensurePatientOwnsAppointment($appointment);

        $appointment->load(['doctor', 'service', 'payments']);

        return view('patient.appointments.show', [
            'appointment' => $appointment,
        ]);
    }

    public function editReschedule(Appointment $appointment): View
    {
        $this->ensurePatientOwnsAppointment($appointment);
        $this->ensureCanChangeAppointment($appointment);

        $appointment->load(['doctor', 'service']);

        return view('patient.appointments.reschedule', [
            'appointment' => $appointment,
        ]);
    }

    public function updateReschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->ensurePatientOwnsAppointment($appointment);
        $this->ensureCanChangeAppointment($appointment);

        $data = $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required'],
        ]);

        $appointment->fill([
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'status' => 'rescheduled',
            'reminder_sent_at' => null,
        ])->save();

        $appointment->load(['patient:id,name,email', 'doctor:id,name', 'service:id,name']);
        if ($appointment->patient && filled($appointment->patient->email)) {
            Notification::send($appointment->patient, new AppointmentRescheduledPatientNotification($appointment));
        }

        return redirect()
            ->route('patient.appointments.show', $appointment)
            ->with('success', 'Appointment rescheduled.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->ensurePatientOwnsAppointment($appointment);
        $this->ensureCanChangeAppointment($appointment);

        $appointment->update(['status' => 'cancelled']);

        return redirect()
            ->route('patient.appointments')
            ->with('success', 'Appointment cancelled.');
    }

    private function ensurePatientOwnsAppointment(Appointment $appointment): void
    {
        if ((int) $appointment->patient_id !== (int) Auth::id()) {
            abort(404);
        }
    }

    private function ensureCanChangeAppointment(Appointment $appointment): void
    {
        $status = $appointment->status ?? 'pending';
        $allowedStatuses = ['pending', 'confirmed', 'rescheduled'];

        if (! in_array($status, $allowedStatuses, true)) {
            abort(403, 'This appointment cannot be changed.');
        }

        $rawDate = $appointment->getRawOriginal('appointment_date');
        if ($rawDate && $rawDate < now()->toDateString()) {
            abort(403, 'Past appointments cannot be changed.');
        }
    }

    /**
     * Doctors who can accept bookings: active status, at least one weekday with schedule on,
     * and when a date is passed, that ISO weekday (Mon=1…Sun=7) must be on and the date must not be blocked.
     */
    private function bookableDoctorsQuery(?string $appointmentDate = null, ?int $serviceId = null): Builder
    {
        $q = Doctor::query()
            ->where('status', 'active')
            ->whereHas('weeklySchedules', fn (Builder $sub) => $sub->where('is_active', true));

        if ($serviceId !== null && DB::table('doctor_service')->where('service_id', $serviceId)->exists()) {
            $q->whereHas('services', fn (Builder $sub) => $sub->where('services.id', $serviceId));
        }

        if ($appointmentDate === null || $appointmentDate === '') {
            return $q->orderBy('name');
        }

        try {
            $weekday = (int) Carbon::parse($appointmentDate)->format('N');
        } catch (\Throwable) {
            return $q->whereRaw('1 = 0')->orderBy('name');
        }

        $q->whereHas('weeklySchedules', fn (Builder $sub) => $sub
            ->where('weekday', $weekday)
            ->where('is_active', true));

        $q->whereDoesntHave('blockedDates', fn (Builder $sub) => $sub
            ->whereDate('blocked_date', $appointmentDate));

        return $q->orderBy('name');
    }

    private function generateAppointmentNo(): string
    {
        $year = now()->format('Y');
        $prefix = 'APT-'.$year.'-';

        $last = Appointment::query()
            ->where('appointment_no', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('appointment_no');

        $lastSeq = 0;
        if (is_string($last) && str_starts_with($last, $prefix)) {
            $lastSeq = (int) substr($last, strlen($prefix));
        }

        return $prefix.str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
    }
}
