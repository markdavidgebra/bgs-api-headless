<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Doctor;
use App\Models\DoctorBlockedDate;
use App\Models\DoctorNotification;
use App\Models\DoctorWeeklySchedule;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Service;
use App\Models\TreatmentPatientPackage;
use App\Support\DoctorPermissions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

trait DoctorPortalResponses
{
    /**
     * @return array<string, mixed>
     */
    protected function doctorPayload(?Doctor $doctor): ?array
    {
        if (! $doctor) {
            return null;
        }

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
            'social_links' => $doctor->social_links,
            'permissions' => DoctorPermissions::forDoctor($doctor),
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
            'doctor_id' => $appointment->doctor_id,
            'service_id' => $appointment->service_id,
            'appointment_date' => $appointment->appointment_date?->toDateString(),
            'appointment_time' => $this->formatAppointmentTime($appointment->appointment_time),
            'status' => $appointment->status,
            'patient' => $appointment->relationLoaded('patient') && $appointment->patient
                ? [
                    'id' => $appointment->patient->id,
                    'name' => $appointment->patient->name,
                    'email' => $appointment->patient->email ?? null,
                    'phone' => $appointment->patient->phone ?? null,
                ]
                : null,
            'service' => $appointment->relationLoaded('service') && $appointment->service
                ? ['id' => $appointment->service->id, 'name' => $appointment->service->name]
                : null,
            'doctor' => $appointment->relationLoaded('doctor') && $appointment->doctor
                ? ['id' => $appointment->doctor->id, 'name' => $appointment->doctor->name]
                : null,
            'has_note' => $appointment->relationLoaded('note')
                ? $appointment->note !== null
                : null,
        ];

        if ($detailed) {
            $payload['note'] = $appointment->relationLoaded('note') && $appointment->note
                ? $this->notePayload($appointment->note)
                : null;
            if ($appointment->relationLoaded('prescribedProducts')) {
                $payload['prescribed_products'] = $appointment->prescribedProducts
                    ->map(fn (Product $p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'quantity' => (int) ($p->pivot->quantity ?? 1),
                    ])
                    ->values()
                    ->all();
            }
            if ($appointment->relationLoaded('timelines')) {
                $payload['timelines'] = $appointment->timelines
                    ->map(fn ($t) => [
                        'id' => $t->id,
                        'event' => $t->event,
                        'event_at' => $t->event_at?->toIso8601String(),
                    ])
                    ->values()
                    ->all();
            }
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function notePayload(AppointmentNote $note): array
    {
        return [
            'id' => $note->id,
            'appointment_id' => $note->appointment_id,
            'patient_concern' => $note->patient_concern,
            'appointment_remarks' => $note->appointment_remarks,
            'admin_notes' => $note->admin_notes,
            'doctor_notes' => $note->doctor_notes,
            'instructions' => $note->instructions,
            'alerts' => $note->alerts,
            'mobility' => $note->mobility,
            'mobility_label' => $note->mobilityLabel(),
            'vital_blood_pressure' => $note->vital_blood_pressure,
            'vital_heart_rate' => $note->vital_heart_rate,
            'vital_temperature' => $note->vital_temperature,
            'vital_respiratory_rate' => $note->vital_respiratory_rate,
            'vital_oxygen_saturation' => $note->vital_oxygen_saturation,
            'vital_weight' => $note->vital_weight,
            'vital_height' => $note->vital_height,
            'body_analyzer_image_url' => $note->bodyAnalyzerImageUrl(),
            'bottle_citrus_image_url' => $note->bottleCitrusImageUrl(),
            'lemon_bottle_image_url' => $note->lemonBottleImageUrl(),
            'aqualyx_image_url' => $note->aqualyxImageUrl(),
            'drip_image_url' => $note->dripImageUrl(),
            'micro_needling_image_url' => $note->microNeedlingImageUrl(),
            'section_authors' => $note->section_authors,
            'has_clinical_content' => AppointmentNote::hasClinicalContent($note),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function notificationPayload(DoctorNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'appointment' => $notification->relationLoaded('appointment') && $notification->appointment
                ? $this->appointmentPayload($notification->appointment)
                : null,
            'patient' => $notification->relationLoaded('patient') && $notification->patient
                ? ['id' => $notification->patient->id, 'name' => $notification->patient->name]
                : null,
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
            'phone' => $patient->phone,
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
            'sku' => $product->sku,
            'unit' => $product->unit,
            'stock_quantity' => (int) $product->stock_quantity,
            'minimum_stock_alert' => (int) $product->minimum_stock_alert,
            'stock_status' => $product->stock_status,
            'selling_price' => $product->selling_price !== null ? (float) $product->selling_price : null,
            'discount_price' => $product->discount_price !== null ? (float) $product->discount_price : null,
            'category' => $product->relationLoaded('categoryItem') && $product->categoryItem
                ? $product->categoryItem->name
                : $product->category,
            'image_url' => $product->image_url,
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
            'status' => $service->status,
            'price' => $service->price !== null ? (float) $service->price : null,
            'promo_price' => $service->promo_price !== null ? (float) $service->promo_price : null,
            'effective_price' => (float) ($service->promo_price ?? $service->price ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function weeklySchedulePayload(DoctorWeeklySchedule $schedule): array
    {
        return [
            'id' => $schedule->id,
            'weekday' => (int) $schedule->weekday,
            'is_active' => (bool) $schedule->is_active,
            'start_time' => $schedule->start_time ? substr((string) $schedule->start_time, 0, 5) : null,
            'end_time' => $schedule->end_time ? substr((string) $schedule->end_time, 0, 5) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function blockedDatePayload(DoctorBlockedDate $blocked): array
    {
        return [
            'id' => $blocked->id,
            'blocked_date' => $blocked->blocked_date?->toDateString(),
            'reason' => $blocked->reason,
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
