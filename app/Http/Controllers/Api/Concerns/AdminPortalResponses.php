<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\AppointmentTimeline;
use App\Models\ClinicalStaff;
use App\Models\Inquiry;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Support\AdminPermissions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

trait AdminPortalResponses
{
    /**
     * @return array<string, mixed>|null
     */
    protected function adminPayload(?Admin $admin): ?array
    {
        if (! $admin) {
            return null;
        }

        return [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => $admin->role,
            'status' => $admin->status,
            'image_url' => $admin->image_url,
            'initial' => $admin->initial,
            'approved_at' => $admin->approved_at?->toIso8601String(),
            'permissions' => AdminPermissions::forAdmin($admin),
            'can_approve_appointments' => AdminPermissions::canApproveAppointments($admin),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function appointmentPayload(Appointment $appointment, bool $detailed = false): array
    {
        $payload = [
            'id' => $appointment->id,
            'appointment_no' => $appointment->appointment_no,
            'patient_id' => $appointment->patient_id,
            'clinical_staff_id' => $appointment->clinical_staff_id,
            'assigned_admin_id' => $appointment->assigned_admin_id,
            'service_id' => $appointment->service_id,
            'appointment_date' => $appointment->appointment_date?->toDateString(),
            'appointment_time' => $this->formatAppointmentTime($appointment->appointment_time),
            'status' => $appointment->status,
            'booking_source' => $appointment->booking_source ?? null,
            'patient' => $appointment->relationLoaded('patient') && $appointment->patient
                ? $this->patientSummaryPayload($appointment->patient)
                : null,
            'clinical_staff' => $this->appointmentAssigneePayload($appointment),
            'service' => $appointment->relationLoaded('service') && $appointment->service
                ? ['id' => $appointment->service->id, 'name' => $appointment->service->name]
                : null,
            'created_at' => $appointment->created_at?->toIso8601String(),
            'updated_at' => $appointment->updated_at?->toIso8601String(),
        ];

        if ($detailed) {
            $payload['created_by_admin'] = $appointment->relationLoaded('createdByAdmin') && $appointment->createdByAdmin
                ? ['id' => $appointment->createdByAdmin->id, 'name' => $appointment->createdByAdmin->name]
                : null;
            $payload['updated_by_admin'] = $appointment->relationLoaded('updatedByAdmin') && $appointment->updatedByAdmin
                ? ['id' => $appointment->updatedByAdmin->id, 'name' => $appointment->updatedByAdmin->name]
                : null;
        }

        return $payload;
    }

    /**
     * @return array{id: int, name: string, type: string}|null
     */
    protected function appointmentAssigneePayload(Appointment $appointment): ?array
    {
        if ($appointment->relationLoaded('clinicalStaff') && $appointment->clinicalStaff) {
            return [
                'id' => (int) $appointment->clinicalStaff->id,
                'name' => (string) $appointment->clinicalStaff->name,
                'type' => 'clinical_staff',
            ];
        }

        if ($appointment->relationLoaded('assignedAdmin') && $appointment->assignedAdmin) {
            return [
                'id' => (int) $appointment->assignedAdmin->id,
                'name' => (string) $appointment->assignedAdmin->name,
                'type' => 'manager',
            ];
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function appointmentNotePayload(AppointmentNote $note): array
    {
        return [
            'id' => $note->id,
            'appointment_id' => $note->appointment_id,
            'patient_concern' => $note->patient_concern,
            'appointment_remarks' => $note->appointment_remarks,
            'admin_notes' => $note->admin_notes,
            'clinical_notes' => $note->clinical_notes,
            'instructions' => $note->instructions,
            'alerts' => $note->alerts,
            'section_authors' => $note->section_authors,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function appointmentPaymentPayload(AppointmentPayment $payment): array
    {
        return [
            'id' => $payment->id,
            'appointment_id' => $payment->appointment_id,
            'invoice_no' => $payment->invoice_no,
            'amount' => $payment->amount !== null ? (float) $payment->amount : null,
            'payment_method' => $payment->payment_method,
            'payment_status' => $payment->payment_status,
            'is_paid' => (bool) $payment->is_paid,
            'deposit_notes' => $payment->deposit_notes,
            'reference_no' => $payment->reference_no,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function appointmentTimelinePayload(AppointmentTimeline $timeline): array
    {
        return [
            'id' => $timeline->id,
            'appointment_id' => $timeline->appointment_id,
            'event' => $timeline->event,
            'description' => $timeline->description,
            'event_at' => $timeline->event_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function patientSummaryPayload(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'name' => $patient->name,
            'email' => $patient->email,
            'phone' => $patient->phone ?? null,
            'status' => $patient->status ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function staffPayload(Admin $staff): array
    {
        return [
            'id' => $staff->id,
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => $staff->role,
            'status' => $staff->status,
            'image_url' => $staff->image_url,
            'approved_at' => $staff->approved_at?->toIso8601String(),
            'created_at' => $staff->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function clinicalStaffPayload(ClinicalStaff $doctor): array
    {
        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'specialty' => $doctor->specialty,
            'license_no' => $doctor->license_no,
            'experience_years' => $doctor->experience_years,
            'bio' => $doctor->bio,
            'status' => $doctor->status,
            'image_url' => $doctor->image_url,
            'image_path' => $doctor->image_path,
            'clinical_staff_role_id' => $doctor->clinical_staff_role_id,
            'clinical_staff_role' => $doctor->relationLoaded('role') && $doctor->role
                ? ['id' => $doctor->role->id, 'name' => $doctor->role->name]
                : null,
            'approved_at' => $doctor->approved_at?->toIso8601String(),
            'created_at' => $doctor->created_at?->toIso8601String(),
        ];
    }

    /**
     * Full doctor profile for admin show (tabs: overview, services, schedule, appointments).
     *
     * @return array<string, mixed>
     */
    protected function clinicalStaffShowPayload(ClinicalStaff $doctor): array
    {
        $payload = $this->clinicalStaffPayload($doctor);

        $payload['assigned_services'] = $doctor->relationLoaded('services')
            ? $doctor->services->pluck('name')->values()->all()
            : [];

        $payload['weekly_schedules'] = $doctor->relationLoaded('weeklySchedules')
            ? $doctor->weeklySchedules
                ->sortBy('weekday')
                ->values()
                ->map(static fn ($schedule) => [
                    'weekday' => (int) $schedule->weekday,
                    'day_label' => $schedule->day_label,
                    'is_active' => (bool) $schedule->is_active,
                    'time_slot_label' => $schedule->time_slot_label,
                ])
                ->all()
            : [];

        $payload['recent_appointments_sample'] = Appointment::query()
            ->where('clinical_staff_id', $doctor->id)
            ->with('patient:id,name')
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->limit(25)
            ->get()
            ->map(static fn (Appointment $appointment) => [
                'code' => $appointment->appointment_no ?? '—',
                'patient' => $appointment->patient?->name ?? '—',
                'date' => $appointment->date_display,
                'time' => $appointment->time_display,
                'status' => $appointment->status_label,
            ])
            ->values()
            ->all();

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function inquiryPayload(Inquiry $inquiry): array
    {
        return [
            'id' => $inquiry->id,
            'name' => $inquiry->name,
            'email' => $inquiry->email,
            'phone' => $inquiry->phone ?? null,
            'preferred_date' => $inquiry->preferred_date ?? null,
            'message' => $inquiry->message ?? null,
            'created_at' => $inquiry->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function paymentPayload(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'payment_no' => $payment->payment_no ?? null,
            'patient_id' => $payment->patient_id,
            'amount' => $payment->amount !== null ? (float) $payment->amount : null,
            'payment_status' => $payment->payment_status,
            'payment_method' => $payment->payment_method,
            'payment_date' => $payment->payment_date?->toDateString(),
            'patient' => $payment->relationLoaded('patient') && $payment->patient
                ? $this->patientSummaryPayload($payment->patient)
                : null,
            'created_at' => $payment->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function servicePayload(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug ?? null,
            'status' => $service->status,
            'price' => $service->price !== null ? (float) $service->price : null,
            'promo_price' => $service->promo_price !== null ? (float) $service->promo_price : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function productPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug ?? null,
            'sku' => $product->sku,
            'status' => $product->status ?? null,
            'stock_quantity' => (int) $product->stock_quantity,
            'minimum_stock_alert' => (int) $product->minimum_stock_alert,
            'selling_price' => $product->selling_price !== null ? (float) $product->selling_price : null,
            'discount_price' => $product->discount_price !== null ? (float) $product->discount_price : null,
            'image_url' => $product->image_url ?? null,
        ];
    }

    /**
     * @param  LengthAwarePaginator<mixed>  $paginator
     * @return array<string, int>
     */
    protected function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    protected function formatAppointmentTime(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_string($raw) && strlen($raw) >= 5) {
            return substr($raw, 0, 5);
        }

        return Carbon::parse($raw)->format('H:i');
    }
}
