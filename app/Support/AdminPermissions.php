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
                ['key' => 'doctors.manage', 'label' => 'Clinical staff'],
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

    public static function isFullAccess(?Admin $admin): bool
    {
        if (! $admin) {
            return false;
        }

        return self::isFullAccessRole((string) ($admin->role ?? ''));
    }

    public static function isFullAccessRole(string $role): bool
    {
        return in_array(self::normalizeRole($role), ['super admin', 'superadmin', 'admin', 'manager'], true);
    }

    public static function isManagerRole(string $role): bool
    {
        return self::normalizeRole($role) === 'manager';
    }

    public static function isManager(?Admin $admin): bool
    {
        return $admin !== null && self::isManagerRole((string) ($admin->role ?? ''));
    }

    public static function canApproveAppointments(?Admin $admin): bool
    {
        return self::isManager($admin);
    }

    /**
     * @return list<string>
     */
    public static function forAdmin(Admin $admin): array
    {
        if (self::isFullAccess($admin)) {
            return self::allKeys();
        }

        $role = self::normalizeRole((string) ($admin->role ?? ''));

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
