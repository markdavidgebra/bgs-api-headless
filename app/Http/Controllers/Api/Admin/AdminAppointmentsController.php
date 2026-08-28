<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Concerns\BooksAppointments;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\AppointmentTimeline;
use App\Models\ClinicalStaff;
use App\Models\Service;
use App\Notifications\Patient\AppointmentBookedPatientNotification;
use App\Rules\BookableAppointmentDate;
use App\Support\AdminPermissions;
use App\Support\ClinicalStaffAppointmentAlerts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class AdminAppointmentsController extends Controller
{
    use AdminPortalResponses;
    use BooksAppointments;

    public function index(Request $request): JsonResponse
    {
        $query = Appointment::query()
            ->with([
                'patient:id,name,email',
                'doctor:id,name',
                'service:id,name',
            ])
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('appointment_no', 'like', '%'.$term.'%')
                    ->orWhereHas('patient', function ($q) use ($term) {
                        $q->where('name', 'like', '%'.$term.'%')
                            ->orWhere('email', 'like', '%'.$term.'%');
                    })
                    ->orWhereHas('doctor', fn ($q) => $q->where('name', 'like', '%'.$term.'%'))
                    ->orWhereHas('service', fn ($q) => $q->where('name', 'like', '%'.$term.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->input('date'));
        }

        $perPage = max(1, min((int) $request->integer('limit', 15), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (Appointment $a) => $this->appointmentPayload($a))
                ->values(),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $monthInput = (string) $request->input('month', now()->format('Y-m'));
        try {
            $monthCursor = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Throwable) {
            $monthCursor = now()->startOfMonth();
        }

        $start = $monthCursor->copy()->startOfMonth();
        $end = $monthCursor->copy()->endOfMonth();

        $appointments = Appointment::query()
            ->with([
                'patient:id,name',
                'service:id,name',
                'doctor:id,name',
            ])
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $byDate = $appointments
            ->groupBy(static function (Appointment $a): string {
                if (empty($a->appointment_date)) {
                    return '';
                }

                return Carbon::parse((string) $a->appointment_date)->toDateString();
            })
            ->map(fn ($group) => $group->map(fn (Appointment $a) => $this->appointmentPayload($a))->values())
            ->all();

        return response()->json([
            'month' => $monthCursor->format('Y-m'),
            'prev_month' => $monthCursor->copy()->subMonth()->format('Y-m'),
            'next_month' => $monthCursor->copy()->addMonth()->format('Y-m'),
            'appointments_by_date' => $byDate,
        ]);
    }

    /**
     * Form data for the "book for a client" screen: bookable services plus
     * doctors available for the (optional) date/service already chosen.
     */
    public function bookOptions(Request $request): JsonResponse
    {
        $services = Service::query()
            ->where('status', 'active')
            ->where('is_bookable', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes', 'price']);

        $date = $request->query('date');
        $serviceId = $request->query('service_id');
        $doctors = $this->bookableDoctorsQuery($date, $serviceId ? (int) $serviceId : null)
            ->get(['id', 'name', 'specialty']);

        return response()->json([
            'services' => $services->map(fn (Service $s) => [
                'id' => (int) $s->id,
                'name' => (string) $s->name,
                'duration_minutes' => $s->duration_minutes !== null ? (int) $s->duration_minutes : null,
                'price' => $s->price !== null ? (float) $s->price : null,
            ])->values(),
            'doctors' => $doctors->map(fn (ClinicalStaff $d) => [
                'id' => (int) $d->id,
                'name' => (string) $d->name,
                'specialty' => (string) ($d->specialty ?? ''),
            ])->values(),
            'statuses' => ['confirmed', 'pending'],
        ]);
    }

    /**
     * Refresh the doctor list when the date and/or service change on the booking form.
     */
    public function bookableDoctors(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today', new BookableAppointmentDate],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ]);

        $doctors = $this->bookableDoctorsQuery($data['date'], isset($data['service_id']) ? (int) $data['service_id'] : null)
            ->get(['id', 'name', 'specialty']);

        return response()->json([
            'doctors' => $doctors->map(fn (ClinicalStaff $d) => [
                'id' => (int) $d->id,
                'name' => (string) $d->name,
                'specialty' => (string) ($d->specialty ?? ''),
            ])->values(),
        ]);
    }

    /**
     * Book an appointment on behalf of a client (walk-in / phone booking).
     * Mirrors the patient self-service booking rules in {@see \App\Http\Controllers\Api\PatientPortalController::bookAppointment()}.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:users,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'doctor_id' => ['required', 'integer', 'exists:clinical_staff,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today', new BookableAppointmentDate],
            'appointment_time' => ['required', 'date_format:H:i'],
            'status' => ['nullable', 'in:pending,confirmed'],
            'patient_concern' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $this->bookableDoctorsQuery($data['appointment_date'], (int) $data['service_id'])
            ->whereKey((int) $data['doctor_id'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'doctor_id' => 'This clinical staff member is not available on the selected date for the selected service.',
            ]);
        }

        $admin = $request->user('admin');
        $isManager = AdminPermissions::canApproveAppointments($admin);
        $status = $isManager
            ? ($data['status'] ?? 'confirmed')
            : 'pending';

        $appointment = DB::transaction(function () use ($data, $admin, $status) {
            $appointment = Appointment::create([
                'appointment_no' => $this->generateAppointmentNo(),
                'patient_id' => (int) $data['patient_id'],
                'doctor_id' => (int) $data['doctor_id'],
                'service_id' => (int) $data['service_id'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'status' => $status,
                'created_by' => $admin?->id,
            ]);

            if (! empty($data['patient_concern'])) {
                AppointmentNote::create([
                    'appointment_id' => $appointment->id,
                    'patient_concern' => $data['patient_concern'],
                    'section_authors' => [
                        'patient_concern' => AppointmentNote::authorPayloadFromUserName('admin', $admin?->name),
                    ],
                ]);
            }

            return $appointment;
        });

        $appointment->load(['patient:id,name,email', 'doctor:id,name', 'service:id,name', 'createdByAdmin:id,name']);

        try {
            if ($appointment->patient) {
                Notification::send($appointment->patient, new AppointmentBookedPatientNotification($appointment));
            }
            ClinicalStaffAppointmentAlerts::notifyDoctorOfNewBooking($appointment);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Appointment booked successfully.',
            'id' => (int) $appointment->id,
            'appointment' => $this->appointmentPayload($appointment, true),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $appointment = Appointment::query()
            ->with([
                'patient',
                'doctor',
                'service',
                'createdByAdmin:id,name',
                'updatedByAdmin:id,name',
            ])
            ->findOrFail($id);

        $note = AppointmentNote::query()
            ->where('appointment_id', $appointment->id)
            ->first();

        $payment = AppointmentPayment::query()
            ->where('appointment_id', $appointment->id)
            ->orderByDesc('id')
            ->first();

        $timelines = AppointmentTimeline::query()
            ->where('appointment_id', $appointment->id)
            ->orderByDesc('event_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'appointment' => $this->appointmentPayload($appointment, true),
            'note' => $note ? $this->appointmentNotePayload($note) : null,
            'payment' => $payment ? $this->appointmentPaymentPayload($payment) : null,
            'timelines' => $timelines->map(fn ($t) => $this->appointmentTimelinePayload($t))->values(),
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        $admin = request()->user('admin');
        if (! AdminPermissions::canApproveAppointments($admin)) {
            return response()->json([
                'message' => __('Only a manager can approve appointments.'),
            ], 403);
        }

        $appointment = Appointment::query()->findOrFail($id);
        $status = strtolower((string) ($appointment->status ?? ''));
        if (! in_array($status, ['pending', 'rescheduled'], true)) {
            return response()->json([
                'message' => __('This appointment cannot be approved.'),
            ], 422);
        }

        $appointment->update([
            'status' => 'confirmed',
            'updated_by' => $admin?->id,
        ]);
        $appointment->load(['patient:id,name,email', 'doctor:id,name', 'service:id,name']);

        return response()->json([
            'message' => __('Appointment approved successfully.'),
            'appointment' => $this->appointmentPayload($appointment, true),
        ]);
    }
}
