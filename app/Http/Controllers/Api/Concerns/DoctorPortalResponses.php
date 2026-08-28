<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Doctor;
use App\Models\DoctorNote;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Support\DoctorPermissions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait DoctorPortalResponses
{
    /**
     * @return array<string, mixed>|null
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
            'prc_expiry' => $doctor->prc_expiry?->toDateString(),
            'ptr_no' => $doctor->ptr_no,
            's2_license_no' => $doctor->s2_license_no,
            'bio' => $doctor->bio,
            'status' => $doctor->status,
            'image_url' => $doctor->image_url,
            'signature_url' => $doctor->signature_url,
            'approved_at' => $doctor->approved_at?->toIso8601String(),
            'permissions' => DoctorPermissions::forDoctor($doctor),
        ];
    }

    /**
     * Short reference used inside prescriptions and notes.
     *
     * @return array<string, mixed>|null
     */
    protected function doctorRefPayload(?Doctor $doctor): ?array
    {
        if (! $doctor) {
            return null;
        }

        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'specialty' => $doctor->specialty,
            'license_no' => $doctor->license_no,
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
     * Full demographics + intake info a doctor needs when opening a chart.
     *
     * @return array<string, mixed>
     */
    protected function patientProfilePayload(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'name' => $patient->name,
            'email' => $patient->email,
            'phone' => $patient->phone,
            'status' => $patient->status,
            'birthdate' => $patient->birthdate?->toDateString(),
            'age' => $patient->age !== null ? (int) $patient->age : null,
            'gender' => $patient->gender,
            'address' => $patient->address,
            'emergency_contact' => $patient->emergency_contact,
            'history_summary' => $patient->history_summary,
            'skin_type' => $patient->skin_type,
            'skin_concerns' => $patient->skin_concerns,
            'recovery_time' => $patient->recovery_time,
            'notes' => $patient->notes,
            'avatar_url' => $patient->avatar_url,
            'created_at' => $patient->created_at?->toIso8601String(),
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
            'clinical_staff' => $appointment->relationLoaded('clinicalStaff') && $appointment->clinicalStaff
                ? ['id' => $appointment->clinicalStaff->id, 'name' => $appointment->clinicalStaff->name]
                : null,
            'has_note' => $appointment->relationLoaded('note')
                ? $appointment->note !== null
                : null,
        ];

        if ($detailed) {
            $payload['note'] = $appointment->relationLoaded('note') && $appointment->note
                ? $this->notePayload($appointment->note)
                : null;
        }

        return $payload;
    }

    /**
     * Everything the clinical staff recorded for one visit. Mirrors
     * {@see ClinicalStaffPortalResponses::notePayload()} so the doctor portal sees
     * exactly the same clinical data as the staff portal.
     *
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
            'clinical_notes' => $note->clinical_notes,
            'instructions' => $note->instructions,
            'alerts' => $note->alerts,
            'mobility' => $note->mobility,
            'mobility_label' => $note->mobilityLabel(),
            'iv_line_type' => $note->iv_line_type,
            'iv_line_type_label' => $note->ivLineTypeLabel(),
            'procedure_drip' => (bool) $note->procedure_drip,
            'procedure_peptides' => (bool) $note->procedure_peptides,
            'informed_consent' => $note->informed_consent,
            'informed_consent_label' => $note->informedConsentLabel(),
            'drip_type' => $note->drip_type,
            'drip_nod' => $note->drip_nod,
            'drip_remarks' => $note->drip_remarks,
            'peptides_type' => $note->peptides_type,
            'peptides_routes' => is_array($note->peptides_routes) ? $note->peptides_routes : [],
            'peptides_route_labels' => $note->peptidesRouteLabels(),
            'peptides_md' => $note->peptides_md,
            'peptides_remarks' => $note->peptides_remarks,
            'has_reaction' => $note->has_reaction,
            'has_reaction_label' => $note->hasReactionLabel(),
            'reaction_time' => $note->reaction_time,
            'reaction_referred' => $note->reaction_referred,
            'reaction_notes' => $note->reaction_notes,
            'reaction_md' => $note->reaction_md,
            'consent_letter' => $note->consent_letter,
            'consent_sent_at' => $note->consent_sent_at?->toIso8601String(),
            'consent_signature_data' => $note->consent_signature_data,
            'consent_signed_at' => $note->consent_signed_at?->toIso8601String(),
            'consent_signer_name' => $note->consent_signer_name,
            'has_assessment_checklist_content' => AppointmentNote::hasAssessmentChecklistContent($note),
            'vital_blood_pressure' => $note->vital_blood_pressure,
            'vital_heart_rate' => $note->vital_heart_rate,
            'vital_temperature' => $note->vital_temperature,
            'vital_respiratory_rate' => $note->vital_respiratory_rate,
            'vital_oxygen_saturation' => $note->vital_oxygen_saturation,
            'vital_weight' => $note->vital_weight,
            'vital_height' => $note->vital_height,
            'vital_signs' => $note->resolvedVitalSigns(),
            'vital_signs_summary' => $note->vitalSignsSummary(),
            'vital_signs_recorded_by' => $note->vitalSignsRecorderLabel(),
            'documented_by' => $note->documentationRecorderLabel(),
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
    protected function doctorNotePayload(DoctorNote $note): array
    {
        return [
            'id' => $note->id,
            'patient_id' => $note->patient_id,
            'doctor_id' => $note->doctor_id,
            'appointment_id' => $note->appointment_id,
            'note' => $note->note,
            'diagnosis' => $note->diagnosis,
            'plan' => $note->plan,
            'created_at' => $note->created_at?->toIso8601String(),
            'updated_at' => $note->updated_at?->toIso8601String(),
            'patient' => $note->relationLoaded('patient') && $note->patient
                ? $this->patientSummaryPayload($note->patient)
                : null,
            'doctor' => $note->relationLoaded('doctor')
                ? $this->doctorRefPayload($note->doctor)
                : null,
            'appointment' => $note->relationLoaded('appointment') && $note->appointment
                ? $this->appointmentPayload($note->appointment)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function prescriptionPayload(Prescription $prescription, bool $withItems = true): array
    {
        $payload = [
            'id' => $prescription->id,
            'prescription_no' => $prescription->prescription_no,
            'patient_id' => $prescription->patient_id,
            'doctor_id' => $prescription->doctor_id,
            'appointment_id' => $prescription->appointment_id,
            'issued_at' => $prescription->issued_at?->toIso8601String(),
            'diagnosis' => $prescription->diagnosis,
            'notes' => $prescription->notes,
            'status' => $prescription->status,
            'created_at' => $prescription->created_at?->toIso8601String(),
            'updated_at' => $prescription->updated_at?->toIso8601String(),
            'patient' => $prescription->relationLoaded('patient') && $prescription->patient
                ? $this->patientSummaryPayload($prescription->patient)
                : null,
            'doctor' => $prescription->relationLoaded('doctor')
                ? $this->doctorRefPayload($prescription->doctor)
                : null,
            'appointment' => $prescription->relationLoaded('appointment') && $prescription->appointment
                ? $this->appointmentPayload($prescription->appointment)
                : null,
        ];

        if ($withItems && $prescription->relationLoaded('items')) {
            $payload['items'] = $prescription->items
                ->map(fn (PrescriptionItem $item) => $this->prescriptionItemPayload($item))
                ->values()
                ->all();
            $payload['items_count'] = $prescription->items->count();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function prescriptionItemPayload(PrescriptionItem $item): array
    {
        return [
            'id' => $item->id,
            'prescription_id' => $item->prescription_id,
            'medication_id' => $item->medication_id,
            'medication_name' => $item->medication_name,
            'strength' => $item->strength,
            'form' => $item->form,
            'route' => $item->route,
            'dosage' => $item->dosage,
            'frequency' => $item->frequency,
            'duration' => $item->duration,
            'quantity' => $item->quantity !== null ? (int) $item->quantity : null,
            'instructions' => $item->instructions,
            'sort_order' => (int) $item->sort_order,
            'display_label' => $this->prescriptionItemLabel($item),
        ];
    }

    protected function prescriptionItemLabel(PrescriptionItem $item): string
    {
        $parts = array_filter([
            trim((string) $item->medication_name),
            trim((string) $item->strength),
            trim((string) $item->form),
        ], static fn (string $value): bool => $value !== '');

        return implode(' ', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    protected function medicationPayload(Medication $medication): array
    {
        return [
            'id' => $medication->id,
            'name' => $medication->name,
            'generic_name' => $medication->generic_name,
            'strength' => $medication->strength,
            'form' => $medication->form,
            'route' => $medication->route,
            'notes' => $medication->notes,
            'is_controlled' => (bool) $medication->is_controlled,
            'status' => $medication->status,
            'label' => $medication->label,
        ];
    }

    /**
     * Flatten every phased vital-sign reading recorded across a patient's visits into a
     * single chronological list, newest visit first.
     *
     * @param  Collection<int, Appointment>  $appointments  Must have `note`, `service` and `clinicalStaff` loaded.
     * @return list<array<string, mixed>>
     */
    protected function vitalSignsTimeline(Collection $appointments): array
    {
        $rows = [];

        foreach ($appointments as $appointment) {
            $note = $appointment->note;
            if ($note === null) {
                continue;
            }

            $resolved = $note->resolvedVitalSigns();
            $context = [
                'appointment_id' => $appointment->id,
                'appointment_no' => $appointment->appointment_no,
                'appointment_date' => $appointment->appointment_date?->toDateString(),
                'appointment_time' => $this->formatAppointmentTime($appointment->appointment_time),
                'appointment_status' => $appointment->status,
                'service' => $appointment->service?->name,
                'clinical_staff' => $appointment->clinicalStaff?->name,
                'recorded_by' => $note->vitalSignsRecorderLabel(),
                'note_id' => $note->id,
            ];

            foreach (AppointmentNote::vitalSignPhases() as $phase) {
                $fields = is_array($resolved[$phase] ?? null) ? $resolved[$phase] : [];
                $row = $this->vitalTimelineRow($context, $phase, null, $fields);
                if ($row !== null) {
                    $rows[] = $row;
                }
            }

            foreach ($resolved['extra'] ?? [] as $reading) {
                if (! is_array($reading)) {
                    continue;
                }
                $row = $this->vitalTimelineRow(
                    $context,
                    'extra',
                    AppointmentNote::normalizeNoteValue($reading['time'] ?? null),
                    $reading,
                );
                if ($row !== null) {
                    $rows[] = $row;
                }
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>|null  Null when the reading is entirely empty.
     */
    private function vitalTimelineRow(array $context, string $phase, ?string $readingTime, array $fields): ?array
    {
        $values = [];
        $hasValue = false;

        foreach (AppointmentNote::vitalSignFieldKeys() as $key) {
            $normalized = AppointmentNote::normalizeNoteValue($fields[$key] ?? null);
            $values[$key] = $normalized;
            if ($normalized !== null) {
                $hasValue = true;
            }
        }

        if (! $hasValue) {
            return null;
        }

        $parts = AppointmentNote::formatVitalFieldParts($values);

        return $context + [
            'phase' => $phase,
            'reading_time' => $readingTime,
            'reading_id' => is_string($fields['id'] ?? null) ? $fields['id'] : null,
            'summary' => implode('; ', $parts),
        ] + $values;
    }

    /**
     * @param  LengthAwarePaginator<mixed>  $paginator
     * @return array<string, int>
     */
    protected function doctorPaginationMeta(LengthAwarePaginator $paginator): array
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
