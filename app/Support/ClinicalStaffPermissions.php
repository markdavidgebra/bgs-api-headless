<?php

namespace App\Support;

use App\Models\ClinicalStaff;

class ClinicalStaffPermissions
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
                ['key' => 'clinical_staff.dashboard', 'label' => 'Dashboard'],
                ['key' => 'clinical_staff.appointments', 'label' => 'Appointments & schedule (all clinic)'],
                ['key' => 'clinical_staff.patient_records', 'label' => 'Patient records'],
                ['key' => 'clinical_staff.treatment_notes', 'label' => 'Treatment notes'],
                ['key' => 'clinical_staff.products', 'label' => 'Clinic products & stock'],
                ['key' => 'clinical_staff.services', 'label' => 'My services'],
                ['key' => 'clinical_staff.availability', 'label' => 'Availability'],
                ['key' => 'clinical_staff.notifications', 'label' => 'Notifications'],
                ['key' => 'clinical_staff.profile', 'label' => 'Profile & password'],
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
    public static function forClinicalStaff(?ClinicalStaff $doctor): array
    {
        if (! $doctor || $doctor->clinical_staff_role_id === null) {
            return self::allKeys();
        }

        $role = $doctor->role;
        if (! $role) {
            return self::allKeys();
        }

        $permissions = is_array($role->permissions) ? $role->permissions : [];
        $allowed = array_map(static fn ($value): string => (string) $value, $permissions);
        $allowed = array_values(array_intersect(self::allKeys(), $allowed));

        if ($allowed === []) {
            return self::allKeys();
        }

        if (! in_array('clinical_staff.appointments', $allowed, true)) {
            $allowed[] = 'clinical_staff.appointments';
        }

        return array_values(array_unique($allowed));
    }

    public static function can(?ClinicalStaff $doctor, string $permission): bool
    {
        if (! $doctor) {
            return false;
        }

        return in_array($permission, self::forClinicalStaff($doctor), true);
    }

    /**
     * Map Laravel route name (clinical_staff.*) to a portal permission key.
     */
    public static function permissionForRouteName(?string $routeName): ?string
    {
        if ($routeName === null || ! str_starts_with($routeName, 'clinical_staff.')) {
            return null;
        }

        return match (true) {
            str_starts_with($routeName, 'clinical_staff.dashboard') => 'clinical_staff.dashboard',
            str_starts_with($routeName, 'clinical_staff.appointments') => 'clinical_staff.appointments',
            str_starts_with($routeName, 'clinical_staff.patient-records') => 'clinical_staff.patient_records',
            str_starts_with($routeName, 'clinical_staff.treatment-notes') => 'clinical_staff.treatment_notes',
            str_starts_with($routeName, 'clinical_staff.products') => 'clinical_staff.products',
            str_starts_with($routeName, 'clinical_staff.services') => 'clinical_staff.services',
            str_starts_with($routeName, 'clinical_staff.availability') => 'clinical_staff.availability',
            str_starts_with($routeName, 'clinical_staff.notifications') => 'clinical_staff.notifications',
            str_starts_with($routeName, 'clinical_staff.profile') => 'clinical_staff.profile',
            default => null,
        };
    }
}
