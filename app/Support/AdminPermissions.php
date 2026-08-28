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
                ['key' => 'inventory_staff.view', 'label' => 'Inventory staff (view list)'],
                ['key' => 'inventory_staff.manage', 'label' => 'Inventory staff (add & edit)'],
                ['key' => 'clinical_staff.view', 'label' => 'Clinical staff (view list)'],
                ['key' => 'clinical_staff.manage', 'label' => 'Clinical staff (add & edit)'],
                ['key' => 'doctors.view', 'label' => 'Doctors (view list)'],
                ['key' => 'doctors.manage', 'label' => 'Doctors (add & edit)'],
                ['key' => 'patients.view', 'label' => 'Patients (view only)'],
                ['key' => 'patients.manage', 'label' => 'Patients (edit & clinical notes)'],
            ],
            'Catalog and Billing' => [
                ['key' => 'services.manage', 'label' => 'Services'],
                ['key' => 'packages.manage', 'label' => 'Treatment packages'],
                ['key' => 'subscriptions.manage', 'label' => 'Subscriptions'],
                ['key' => 'products.manage', 'label' => 'Products'],
                ['key' => 'medications.manage', 'label' => 'Medications'],
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
     * Built-in Admin role: operations only. Manager owns people and roles.
     */
    public static function isBuiltInAdminRole(?Admin $admin): bool
    {
        return $admin !== null && self::normalizeRole((string) ($admin->role ?? '')) === 'admin';
    }

    /**
     * Keys only the Manager (not the built-in Admin role) may hold.
     *
     * @return list<string>
     */
    public static function managerOnlyKeys(): array
    {
        return [
            'staff.manage',
            'roles.manage',
            'clinical_staff.manage',
            'doctors.manage',
            'inventory_staff.manage',
            'medications.manage',
        ];
    }

    /**
     * @return list<string>
     */
    public static function forAdmin(Admin $admin): array
    {
        if (self::isFullAccess($admin)) {
            $allowed = self::allKeys();
        } else {
            $role = self::normalizeRole((string) ($admin->role ?? ''));

            $adminRole = AdminRole::query()->where('role_value', $role)->first();
            if (! $adminRole) {
                return [];
            }

            $permissions = is_array($adminRole->permissions) ? $adminRole->permissions : [];
            $allowed = array_map(static fn ($value): string => (string) $value, $permissions);

            $allowed = array_values(array_intersect(self::allKeys(), $allowed));
        }

        if (self::isBuiltInAdminRole($admin)) {
            $allowed = array_values(array_diff($allowed, self::managerOnlyKeys()));
        }

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

        if (in_array('clinical_staff.manage', $allowed, true) && ! in_array('clinical_staff.view', $allowed, true)) {
            $allowed[] = 'clinical_staff.view';
        }

        if (in_array('doctors.manage', $allowed, true) && ! in_array('doctors.view', $allowed, true)) {
            $allowed[] = 'doctors.view';
        }

        if (in_array('inventory_staff.manage', $allowed, true) && ! in_array('inventory_staff.view', $allowed, true)) {
            $allowed[] = 'inventory_staff.view';
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
