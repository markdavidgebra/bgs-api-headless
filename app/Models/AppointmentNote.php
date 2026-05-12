<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
