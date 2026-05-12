<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\AdminRole;

class AdminPermissions
{
    /**
     * @return array<string, array<int, array{key:string,label:string}>>
     */
    public static function groups(): array
    {
        return [
            'Core' => [
                ['key' => 'dashboard.view', 'label' => 'Dashboard'],
                ['key' => 'roles.manage', 'label' => 'Role management'],
                ['key' => 'settings.manage', 'label' => 'Settings'],
            ],
            'Pages and Content' => [
                ['key' => 'pages.manage', 'label' => 'Pages'],
                ['key' => 'promotions.manage', 'label' => 'Promotions'],
            ],
            'Clinic Operations' => [
                ['key' => 'appointments.manage', 'label' => 'Appointments'],
                ['key' => 'inquiries.manage', 'label' => 'Inquiries'],
                ['key' => 'registrations.manage', 'label' => 'Registrations'],
                ['key' => 'staff.manage', 'label' => 'Staff'],
                ['key' => 'doctors.manage', 'label' => 'Doctors'],
                ['key' => 'patients.view', 'label' => 'Patients (view only)'],
                ['key' => 'patients.manage', 'label' => 'Patients (edit & clinical notes)'],
            ],
            'Catalog and Billing' => [
                ['key' => 'services.manage', 'label' => 'Services'],
                ['key' => 'packages.manage', 'label' => 'Treatment packages'],
                ['key' => 'subscriptions.manage', 'label' => 'Subscriptions'],
                ['key' => 'products.manage', 'label' => 'Products'],
                ['key' => 'payments.manage', 'label' => 'Payments'],
                ['key' => 'reports.view', 'label' => 'Reports'],
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

    public static function canAccess(?Admin $admin, string $permission): bool
    {
        if (! $admin) {
            return false;
        }

        return in_array($permission, self::forAdmin($admin), true);
    }

    /**
     * @return list<string>
     */
    public static function forAdmin(Admin $admin): array
    {
        $role = self::normalizeRole((string) ($admin->role ?? ''));
        if (in_array($role, ['super admin', 'superadmin'], true)) {
            return self::allKeys();
        }

        if ($role === 'admin') {
            return array_values(array_filter(
                self::allKeys(),
                static fn (string $key): bool => $key !== 'pages.manage'
            ));
        }

        $adminRole = AdminRole::query()->where('role_value', $role)->first();
        if (! $adminRole) {
            return [];
        }

        $permissions = is_array($adminRole->permissions) ? $adminRole->permissions : [];
        $allowed = array_map(static fn ($value): string => (string) $value, $permissions);

        $allowed = array_values(array_intersect(self::allKeys(), $allowed));

        return self::expandImpliedPermissions($allowed);
    }

    /**
     * Grant implied keys so older role rows that only store e.g. patients.manage still work.
     *
     * @param  list<string>  $allowed
     * @return list<string>
     */
    private static function expandImpliedPermissions(array $allowed): array
    {
        if (in_array('patients.manage', $allowed, true) && ! in_array('patients.view', $allowed, true)) {
            $allowed[] = 'patients.view';
        }

        return array_values(array_unique($allowed));
    }

    public static function normalizeRole(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[_-]+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^a-z0-9 ]+/', '', $normalized) ?? $normalized;

        return trim($normalized);
    }
}
