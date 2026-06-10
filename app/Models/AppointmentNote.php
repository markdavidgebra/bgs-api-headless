<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AppointmentNote extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentNoteFactory> */
    use HasFactory;

    protected $table = 'appointment_notes';

    protected $fillable = [
        'appointment_id',
        'patient_concern',
        'appointment_remarks',
        'admin_notes',
        'doctor_notes',
        'instructions',
        'alerts',
        'vital_blood_pressure',
        'vital_heart_rate',
        'vital_temperature',
        'vital_respiratory_rate',
        'vital_oxygen_saturation',
        'vital_weight',
        'vital_height',
        'body_analyzer_image_path',
        'bottle_citrus_image_path',
        'lemon_bottle_image_path',
        'aqualyx_image_path',
        'drip_image_path',
        'micro_needling_image_path',
        'section_authors',
        'mobility',
        'iv_line_type',
        'procedure_drip',
        'procedure_peptides',
        'informed_consent',
        'drip_type',
        'drip_nod',
        'drip_remarks',
        'peptides_type',
        'peptides_routes',
        'peptides_md',
        'peptides_remarks',
        'has_reaction',
        'reaction_time',
        'reaction_referred',
        'reaction_notes',
        'reaction_md',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'section_authors' => 'array',
            'procedure_drip' => 'boolean',
            'procedure_peptides' => 'boolean',
            'peptides_routes' => 'array',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Host-agnostic public URL for files on the public disk.
     * The staff portal resolves /storage/... against the correct origin in production.
     */
    public static function publicStoragePathUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $path), '/');
    }

    public function bodyAnalyzerImageUrl(): ?string
    {
        return self::publicStoragePathUrl($this->body_analyzer_image_path);
    }

    public function hasBodyAnalyzerImagePath(): bool
    {
        return filled($this->body_analyzer_image_path);
    }

    public function bottleCitrusImageUrl(): ?string
    {
        return self::publicStoragePathUrl($this->bottle_citrus_image_path);
    }

    public function hasBottleCitrusImagePath(): bool
    {
        return filled($this->bottle_citrus_image_path);
    }

    public function lemonBottleImageUrl(): ?string
    {
        return self::publicStoragePathUrl($this->lemon_bottle_image_path);
    }

    public function hasLemonBottleImagePath(): bool
    {
        return filled($this->lemon_bottle_image_path);
    }

    public function aqualyxImageUrl(): ?string
    {
        return self::publicStoragePathUrl($this->aqualyx_image_path);
    }

    public function hasAqualyxImagePath(): bool
    {
        return filled($this->aqualyx_image_path);
    }

    public function dripImageUrl(): ?string
    {
        return self::publicStoragePathUrl($this->drip_image_path);
    }

    public function hasDripImagePath(): bool
    {
        return filled($this->drip_image_path);
    }

    public function microNeedlingImageUrl(): ?string
    {
        return self::publicStoragePathUrl($this->micro_needling_image_path);
    }

    public function hasMicroNeedlingImagePath(): bool
    {
        return filled($this->micro_needling_image_path);
    }

    /**
     * @return array<string, string> Stored value => display label
     */
    public static function mobilityOptions(): array
    {
        return [
            'ambulatory' => __('Ambulatory'),
            'with_assistive' => __('With assistive device'),
            'wheelchair' => __('Wheelchair'),
        ];
    }

    public function mobilityLabel(): ?string
    {
        $value = $this->mobility;
        if ($value === null || $value === '') {
            return null;
        }

        return self::mobilityOptions()[$value] ?? $value;
    }

    /**
     * @return array<string, string> Stored value => display label
     */
    public static function ivLineTypeOptions(): array
    {
        return [
            'iv_cannula_g16' => __('IV Cannula G16'),
            'scalp_vein' => __('Scalp Vein'),
        ];
    }

    public function ivLineTypeLabel(): ?string
    {
        $value = $this->iv_line_type;
        if ($value === null || $value === '') {
            return null;
        }

        return self::ivLineTypeOptions()[$value] ?? $value;
    }

    /**
     * @return array<string, string> Stored value => display label
     */
    public static function yesNoOptions(): array
    {
        return [
            'yes' => __('Yes'),
            'no' => __('No'),
        ];
    }

    public function informedConsentLabel(): ?string
    {
        $value = $this->informed_consent;
        if ($value === null || $value === '') {
            return null;
        }

        return self::yesNoOptions()[$value] ?? $value;
    }

    public function hasReactionLabel(): ?string
    {
        $value = $this->has_reaction;
        if ($value === null || $value === '') {
            return null;
        }

        return self::yesNoOptions()[$value] ?? $value;
    }

    /**
     * @return array<string, string> Stored value => display label
     */
    public static function peptidesRouteOptions(): array
    {
        return [
            'sq' => __('SQ'),
            'iv' => __('IV'),
            'mg' => __('Mg'),
            'units' => __('Units'),
        ];
    }

    /**
     * @return list<string>
     */
    public function peptidesRouteLabels(): array
    {
        $routes = is_array($this->peptides_routes) ? $this->peptides_routes : [];
        $options = self::peptidesRouteOptions();
        $labels = [];

        foreach ($routes as $route) {
            $key = is_string($route) ? $route : '';
            if ($key === '') {
                continue;
            }
            $labels[] = $options[$key] ?? $key;
        }

        return $labels;
    }

    /**
     * @return list<string>
     */
    public static function assessmentChecklistFieldKeys(): array
    {
        return [
            'mobility',
            'iv_line_type',
            'procedure_drip',
            'procedure_peptides',
            'informed_consent',
            'drip_type',
            'drip_nod',
            'drip_remarks',
            'peptides_type',
            'peptides_routes',
            'peptides_md',
            'peptides_remarks',
            'has_reaction',
            'reaction_time',
            'reaction_referred',
            'reaction_notes',
            'reaction_md',
        ];
    }

    public static function hasAssessmentChecklistContent(?self $note): bool
    {
        if ($note === null) {
            return false;
        }

        if (filled($note->mobility)) {
            return true;
        }

        if (filled($note->iv_line_type)) {
            return true;
        }

        if ($note->procedure_drip || $note->procedure_peptides) {
            return true;
        }

        if (filled($note->informed_consent)) {
            return true;
        }

        foreach ([
            $note->drip_type,
            $note->drip_nod,
            $note->drip_remarks,
            $note->peptides_type,
            $note->peptides_md,
            $note->peptides_remarks,
            $note->has_reaction,
            $note->reaction_time,
            $note->reaction_referred,
            $note->reaction_notes,
            $note->reaction_md,
        ] as $value) {
            if (self::normalizeNoteValue($value) !== null) {
                return true;
            }
        }

        $routes = is_array($note->peptides_routes) ? $note->peptides_routes : [];

        return $routes !== [];
    }

    /**
     * Display name of whoever recorded mobility (from section_authors), not the appointment doctor.
     */
    public function mobilityRecorderLabel(): ?string
    {
        $authors = is_array($this->section_authors) ? $this->section_authors : [];
        $raw = $authors['mobility'] ?? null;

        if (! is_array($raw)) {
            return null;
        }

        $name = self::sectionAuthorDisplayName($raw);
        if ($name !== null) {
            return $name;
        }

        return self::formatSectionAuthorLabel($raw);
    }

    protected static function booted(): void
    {
        static::deleting(function (self $note): void {
            if (filled($note->body_analyzer_image_path)) {
                Storage::disk('public')->delete($note->body_analyzer_image_path);
            }
            if (filled($note->bottle_citrus_image_path)) {
                Storage::disk('public')->delete($note->bottle_citrus_image_path);
            }
            if (filled($note->lemon_bottle_image_path)) {
                Storage::disk('public')->delete($note->lemon_bottle_image_path);
            }
            if (filled($note->aqualyx_image_path)) {
                Storage::disk('public')->delete($note->aqualyx_image_path);
            }
            if (filled($note->drip_image_path)) {
                Storage::disk('public')->delete($note->drip_image_path);
            }
            if (filled($note->micro_needling_image_path)) {
                Storage::disk('public')->delete($note->micro_needling_image_path);
            }
        });
    }

    /**
     * Whether this row has any non-empty clinical or admin text.
     */
    public static function hasClinicalContent(?self $note): bool
    {
        if ($note === null) {
            return false;
        }

        foreach ([
            $note->doctor_notes,
            $note->patient_concern,
            $note->appointment_remarks,
            $note->instructions,
            $note->alerts,
            $note->admin_notes,
        ] as $value) {
            if (self::normalizeNoteValue($value) !== null) {
                return true;
            }
        }

        if (filled($note->body_analyzer_image_path)) {
            return true;
        }

        if (filled($note->bottle_citrus_image_path)) {
            return true;
        }

        if (filled($note->lemon_bottle_image_path)) {
            return true;
        }

        if (filled($note->aqualyx_image_path)) {
            return true;
        }

        if (filled($note->drip_image_path)) {
            return true;
        }

        if (filled($note->micro_needling_image_path)) {
            return true;
        }

        return $note->vitalSignsSummary() !== '';
    }

    /**
     * Compact text for tables (history list, previews).
     */
    public function treatmentSummarySnippet(int $limit = 120): string
    {
        $chunks = [];
        $vitals = $this->vitalSignsSummary();
        if ($vitals !== '') {
            $chunks[] = 'Vitals: '.$vitals;
        }
        if (filled($this->body_analyzer_image_path)) {
            $chunks[] = __('Body analyzer image');
        }
        if (filled($this->bottle_citrus_image_path)) {
            $chunks[] = __('Bottle citrus');
        }
        if (filled($this->lemon_bottle_image_path)) {
            $chunks[] = __('Lemon bottle');
        }
        if (filled($this->aqualyx_image_path)) {
            $chunks[] = __('Aqualyx');
        }
        if (filled($this->drip_image_path)) {
            $chunks[] = __('Drip');
        }
        if (filled($this->micro_needling_image_path)) {
            $chunks[] = __('Micro needling');
        }
        foreach ([
            $this->doctor_notes,
            $this->patient_concern,
            $this->appointment_remarks,
            $this->instructions,
            $this->alerts,
            $this->admin_notes,
        ] as $value) {
            $normalized = self::normalizeNoteValue($value);
            if ($normalized !== null) {
                $chunks[] = $normalized;
            }
        }

        $text = implode(' · ', $chunks);

        return $text === '' ? '' : (string) \Illuminate\Support\Str::limit($text, $limit);
    }

    /**
     * Labeled clinical text fields for patient info (excludes vitals and allergy).
     *
     * @return list<array{label: string, value: string}>
     */
    public function patientInfoClinicalFields(): array
    {
        $fields = [
            ['label' => __('Patient concern'), 'value' => $this->patient_concern],
            ['label' => __('Post procedures'), 'value' => $this->appointment_remarks],
            ['label' => __('Medical history'), 'value' => $this->admin_notes],
            ['label' => __('Doctor notes'), 'value' => $this->doctor_notes],
            ['label' => __('Take home medications'), 'value' => $this->instructions],
        ];

        $out = [];
        foreach ($fields as $field) {
            $normalized = self::normalizeNoteValue($field['value']);
            if ($normalized !== null) {
                $out[] = ['label' => $field['label'], 'value' => $normalized];
            }
        }

        return $out;
    }

    /**
     * Image attachment labels on file (for patient info overview).
     *
     * @return list<string>
     */
    public function patientInfoImageAttachments(): array
    {
        $items = [];
        if (filled($this->body_analyzer_image_path)) {
            $items[] = __('Body analyzer');
        }
        if (filled($this->bottle_citrus_image_path)) {
            $items[] = __('Bottle citrus');
        }
        if (filled($this->lemon_bottle_image_path)) {
            $items[] = __('Lemon bottle');
        }
        if (filled($this->aqualyx_image_path)) {
            $items[] = __('Aqualyx');
        }
        if (filled($this->drip_image_path)) {
            $items[] = __('Drip');
        }
        if (filled($this->micro_needling_image_path)) {
            $items[] = __('Micro needling');
        }

        return $items;
    }

    /**
     * Human-readable vital signs for tables and previews.
     */
    public function vitalSignsSummary(): string
    {
        $parts = [];
        $push = static function (string $label, mixed $value) use (&$parts): void {
            $n = self::normalizeNoteValue($value);
            if ($n !== null) {
                $parts[] = $label.' '.$n;
            }
        };

        $push('BP', $this->vital_blood_pressure ?? null);
        $push('HR', $this->vital_heart_rate ?? null);
        $push('Temp', $this->vital_temperature ?? null);
        $push('RR', $this->vital_respiratory_rate ?? null);
        $push('SpO2', $this->vital_oxygen_saturation ?? null);
        $push('Wt', $this->vital_weight ?? null);
        $push('Ht', $this->vital_height ?? null);

        return implode('; ', $parts);
    }

    /**
     * @return list<string>
     */
    public static function vitalSignFieldKeys(): array
    {
        return [
            'vital_blood_pressure',
            'vital_heart_rate',
            'vital_temperature',
            'vital_respiratory_rate',
            'vital_oxygen_saturation',
            'vital_weight',
            'vital_height',
        ];
    }

    /**
     * Display name of whoever recorded vital signs (from section_authors), not the appointment doctor.
     */
    public function vitalSignsRecorderLabel(): ?string
    {
        $authors = is_array($this->section_authors) ? $this->section_authors : [];

        foreach (self::vitalSignFieldKeys() as $key) {
            $raw = $authors[$key] ?? null;
            if (! is_array($raw)) {
                continue;
            }

            $name = self::sectionAuthorDisplayName($raw);
            if ($name !== null) {
                return $name;
            }

            $label = self::formatSectionAuthorLabel($raw);
            if ($label !== null) {
                return $label;
            }
        }

        return null;
    }

    public static function normalizeNoteValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @return array{first_name: string, last_name: string}
     */
    public static function splitDisplayName(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return ['first_name' => '', 'last_name' => ''];
        }

        $tokens = preg_split('/\s+/u', $name) ?: [];
        $first = (string) ($tokens[0] ?? '');
        $last = count($tokens) > 1 ? trim(implode(' ', array_slice($tokens, 1))) : '';

        return ['first_name' => $first, 'last_name' => $last];
    }

    /**
     * @param  'doctor'|'admin'|'patient'  $type
     * @return array{type: string, first_name: string, last_name: string}
     */
    public static function authorPayloadFromUserName(string $type, ?string $name): array
    {
        $split = self::splitDisplayName($name);

        return [
            'type' => $type,
            'first_name' => $split['first_name'],
            'last_name' => $split['last_name'],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $existingAuthors
     * @param  array<string, mixed>  $oldSnapshot
     * @param  array<string, mixed>  $newSnapshot
     * @param  list<string>  $fieldKeys
     * @param  array{type: string, first_name: string, last_name: string}  $newAuthor
     * @return array<string, array{type: string, first_name: string, last_name: string}>
     */
    public static function mergeAuthorsOnFieldChanges(
        ?array $existingAuthors,
        array $oldSnapshot,
        array $newSnapshot,
        array $fieldKeys,
        array $newAuthor,
    ): array {
        $authors = is_array($existingAuthors) ? $existingAuthors : [];

        foreach ($fieldKeys as $key) {
            $old = self::normalizeNoteValue($oldSnapshot[$key] ?? null);
            $new = self::normalizeNoteValue($newSnapshot[$key] ?? null);

            if ($old === $new) {
                continue;
            }

            if ($new === null) {
                unset($authors[$key]);
            } else {
                $authors[$key] = $newAuthor;
            }
        }

        return $authors;
    }

    /**
     * @param  array{type?: string, first_name?: string, last_name?: string}|null  $author
     */
    public static function sectionAuthorDisplayName(?array $author): ?string
    {
        if ($author === null || $author === []) {
            return null;
        }

        $first = trim((string) ($author['first_name'] ?? ''));
        $last = trim((string) ($author['last_name'] ?? ''));
        $name = trim($first.' '.$last);

        return $name !== '' ? $name : null;
    }

    /**
     * @param  array{type?: string, first_name?: string, last_name?: string}|null  $author
     */
    public static function formatSectionAuthorLabel(?array $author): ?string
    {
        if ($author === null || $author === []) {
            return null;
        }

        $type = (string) ($author['type'] ?? '');
        $first = trim((string) ($author['first_name'] ?? ''));
        $last = trim((string) ($author['last_name'] ?? ''));

        if ($type === 'doctor') {
            $suffix = $last !== '' ? $last : $first;

            return $suffix !== '' ? 'Dr. '.$suffix : null;
        }

        return $first !== '' ? $first : null;
    }

    /**
     * Label for who wrote this section: prefers stored {@see $sectionAuthors}; if missing (legacy rows),
     * uses a best-effort guess from the appointment’s patient or doctor.
     *
     * @param  array<string, mixed>|null  $sectionAuthors
     */
    public static function creatorLabelForSection(
        ?array $sectionAuthors,
        string $fieldKey,
        ?Patient $patient,
        ?Doctor $doctor,
    ): ?string {
        $authors = is_array($sectionAuthors) ? $sectionAuthors : [];
        $raw = $authors[$fieldKey] ?? null;

        if (is_array($raw)) {
            $label = self::formatSectionAuthorLabel($raw);
            if ($label !== null) {
                return $label;
            }

            return match ((string) ($raw['type'] ?? '')) {
                'admin' => __('Staff'),
                'doctor' => self::legacyDoctorLabelFromFullName($doctor?->name),
                'patient' => self::legacyFirstNameFromFullName($patient?->name),
                default => null,
            };
        }

        return match ($fieldKey) {
            'patient_concern' => self::legacyFirstNameFromFullName($patient?->name),
            'admin_notes' => __('Staff'),
            default => self::legacyDoctorLabelFromFullName($doctor?->name),
        };
    }

    private static function legacyFirstNameFromFullName(?string $name): ?string
    {
        $split = self::splitDisplayName($name);

        return $split['first_name'] !== '' ? $split['first_name'] : null;
    }

    private static function legacyDoctorLabelFromFullName(?string $name): ?string
    {
        $split = self::splitDisplayName($name);
        $suffix = $split['last_name'] !== '' ? $split['last_name'] : $split['first_name'];

        return $suffix !== '' ? 'Dr. '.$suffix : null;
    }
}
