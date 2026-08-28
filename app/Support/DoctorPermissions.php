<?php

namespace App\Support;

use App\Models\Doctor;

/**
 * Permission keys for the prescribing doctor portal.
 *
 * v1 deliberately has no doctor role table: every active doctor gets every key.
 * This class exists so the frontend can reuse the same permission-driven nav
 * pattern as the other portals, and so per-role scoping can be introduced later
 * without changing the API contract.
 */
class DoctorPermissions
{
    /**
     * @return array<string, array<int, array{key:string,label:string}>>
     */
    public static function groups(): array
    {
        return [
            'Portal' => [
                ['key' => 'doctor.dashboard', 'label' => 'Dashboard'],
                ['key' => 'doctor.patients', 'label' => 'Patients & clinical records'],
                ['key' => 'doctor.notes', 'label' => 'Doctor notes'],
                ['key' => 'doctor.prescriptions', 'label' => 'Prescriptions'],
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

    /**
     * @return list<string>
     */
    public static function forDoctor(?Doctor $doctor): array
    {
        if (! $doctor || ! $doctor->isActive()) {
            return [];
        }

        return self::allKeys();
    }

    public static function can(?Doctor $doctor, string $permission): bool
    {
        return in_array($permission, self::forDoctor($doctor), true);
    }
}
