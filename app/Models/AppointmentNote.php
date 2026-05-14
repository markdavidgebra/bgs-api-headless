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
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'section_authors' => 'array',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function bodyAnalyzerImageUrl(): ?string
    {
        $path = $this->body_analyzer_image_path;
        if ($path === null || $path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function hasBodyAnalyzerImagePath(): bool
    {
        return filled($this->body_analyzer_image_path);
    }

    public function bottleCitrusImageUrl(): ?string
    {
        $path = $this->bottle_citrus_image_path;
        if ($path === null || $path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function hasBottleCitrusImagePath(): bool
    {
        return filled($this->bottle_citrus_image_path);
    }

    public function lemonBottleImageUrl(): ?string
    {
        $path = $this->lemon_bottle_image_path;
        if ($path === null || $path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function hasLemonBottleImagePath(): bool
    {
        return filled($this->lemon_bottle_image_path);
    }

    public function aqualyxImageUrl(): ?string
    {
        $path = $this->aqualyx_image_path;
        if ($path === null || $path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function hasAqualyxImagePath(): bool
    {
        return filled($this->aqualyx_image_path);
    }

    public function dripImageUrl(): ?string
    {
        $path = $this->drip_image_path;
        if ($path === null || $path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function hasDripImagePath(): bool
    {
        return filled($this->drip_image_path);
    }

    public function microNeedlingImageUrl(): ?string
    {
        $path = $this->micro_needling_image_path;
        if ($path === null || $path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function hasMicroNeedlingImagePath(): bool
    {
        return filled($this->micro_needling_image_path);
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
