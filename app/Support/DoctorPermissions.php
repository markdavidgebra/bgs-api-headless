<?php

namespace App\Support;

use App\Models\Doctor;

class DoctorPermissions
{
    /**
     * Permission keys for the doctor portal (separate from admin Role Management).
     *
     * @return array<string, array<int, array{key:string,label:string}>>
     */
    public static function groups(): array
    {
        return [
            'Portal' => [
                ['key' => 'doctor.dashboard', 'label' => 'Dashboard'],
                ['key' => 'doctor.appointments', 'label' => 'Appointments & schedule (all clinic)'],
                ['key' => 'doctor.patient_records', 'label' => 'Patient records'],
                ['key' => 'doctor.treatment_notes', 'label' => 'Treatment notes'],
                ['key' => 'doctor.products', 'label' => 'Clinic products & stock'],
                ['key' => 'doctor.services', 'label' => 'My services'],
                ['key' => 'doctor.availability', 'label' => 'Availability'],
                ['key' => 'doctor.notifications', 'label' => 'Notifications'],
                ['key' => 'doctor.profile', 'label' => 'Profile & password'],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function allKeys(): array
    {
        $keys = [];
        foreach (self::groups() as $group) {
            foreach ($group as $permission) {
                $keys[] = $permission['key'];
            }
        }

        return $keys;
    }

    public static function normalizeRole(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[_-]+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^a-z0-9 ]+/', '', $normalized) ?? $normalized;

        return trim($normalized);
    }

    /**
     * @return list<string>
     */
    public static function forDoctor(?Doctor $doctor): array
    {
        if (! $doctor || $doctor->doctor_role_id === null) {
            return self::allKeys();
        }

        $role = $doctor->doctorRole;
        if (! $role) {
            return self::allKeys();
        }

        $permissions = is_array($role->permissions) ? $role->permissions : [];
        $allowed = array_map(static fn ($value): string => (string) $value, $permissions);
        $allowed = array_values(array_intersect(self::allKeys(), $allowed));

        if ($allowed === []) {
            return self::allKeys();
        }

        if (! in_array('doctor.appointments', $allowed, true)) {
            $allowed[] = 'doctor.appointments';
        }

        return array_values(array_unique($allowed));
    }

    public static function can(?Doctor $doctor, string $permission): bool
    {
        if (! $doctor) {
            return false;
        }

        return in_array($permission, self::forDoctor($doctor), true);
    }

    /**
     * Map Laravel route name (doctor.*) to a portal permission key.
     */
    public static function permissionForRouteName(?string $routeName): ?string
    {
        if ($routeName === null || ! str_starts_with($routeName, 'doctor.')) {
            return null;
        }

        return match (true) {
            str_starts_with($routeName, 'doctor.dashboard') => 'doctor.dashboard',
            str_starts_with($routeName, 'doctor.appointments') => 'doctor.appointments',
            str_starts_with($routeName, 'doctor.patient-records') => 'doctor.patient_records',
            str_starts_with($routeName, 'doctor.treatment-notes') => 'doctor.treatment_notes',
            str_starts_with($routeName, 'doctor.products') => 'doctor.products',
            str_starts_with($routeName, 'doctor.services') => 'doctor.services',
            str_starts_with($routeName, 'doctor.availability') => 'doctor.availability',
            str_starts_with($routeName, 'doctor.notifications') => 'doctor.notifications',
            str_starts_with($routeName, 'doctor.profile') => 'doctor.profile',
            default => null,
        };
    }
}
