<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\ClinicalStaff;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Approved managers, CEOs, and developers may sign into the clinical-staff and doctor portals.
 * Each manager gets a linked portal identity so those guards keep working
 * without changing every staff/doctor query to also accept the admin guard.
 */
class ManagerPortalAccess
{
    /**
     * @param  array{email?: string, password?: string}  $credentials
     */
    public static function authenticate(array $credentials): ?Admin
    {
        $email = strtolower(trim((string) ($credentials['email'] ?? '')));
        $password = (string) ($credentials['password'] ?? '');
        if ($email === '' || $password === '') {
            return null;
        }

        $admin = Admin::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $admin || ! $admin->isApproved() || ! AdminPermissions::isLeadership($admin)) {
            return null;
        }

        if (! Auth::guard('admin')->validate([
            'email' => (string) $admin->email,
            'password' => $password,
        ])) {
            return null;
        }

        return $admin;
    }

    public static function clinicalStaffIdentity(Admin $manager): ClinicalStaff
    {
        $alias = ClinicalStaff::query()->where('admin_id', $manager->id)->first();
        if ($alias) {
            return self::syncAlias($alias, $manager);
        }

        $existing = ClinicalStaff::query()
            ->whereRaw('LOWER(email) = ?', [strtolower((string) $manager->email)])
            ->first();
        if ($existing) {
            return self::ensureActive($existing);
        }

        return ClinicalStaff::query()->create([
            'admin_id' => $manager->id,
            'name' => (string) $manager->name,
            'email' => (string) $manager->email,
            'password' => Str::password(40),
            'specialty' => self::aliasSpecialty($manager),
            'status' => 'active',
            'approved_at' => now(),
            'clinical_staff_role_id' => null,
        ]);
    }

    public static function doctorIdentity(Admin $manager): Doctor
    {
        $alias = Doctor::query()->where('admin_id', $manager->id)->first();
        if ($alias) {
            return self::syncAlias($alias, $manager);
        }

        $existing = Doctor::query()
            ->whereRaw('LOWER(email) = ?', [strtolower((string) $manager->email)])
            ->first();
        if ($existing) {
            return self::ensureActive($existing);
        }

        return Doctor::query()->create([
            'admin_id' => $manager->id,
            'name' => (string) $manager->name,
            'email' => (string) $manager->email,
            'password' => Str::password(40),
            'specialty' => self::aliasSpecialty($manager),
            'status' => 'active',
            'approved_at' => now(),
        ]);
    }

    /**
     * @param  ClinicalStaff|Doctor  $identity
     * @return ClinicalStaff|Doctor
     */
    private static function syncAlias($identity, Admin $manager)
    {
        $identity->name = (string) $manager->name;
        $identity->email = (string) $manager->email;
        $identity->specialty = self::aliasSpecialty($manager);

        return self::ensureActive($identity);
    }

    /**
     * @param  ClinicalStaff|Doctor  $identity
     * @return ClinicalStaff|Doctor
     */
    private static function ensureActive($identity)
    {
        if (strtolower((string) ($identity->status ?? '')) !== 'active') {
            $identity->status = 'active';
            $identity->approved_at = $identity->approved_at ?? now();
        }
        $identity->save();

        return $identity;
    }

    public static function canApproveAppointments(?ClinicalStaff $staff): bool
    {
        return self::linkedManager($staff) !== null;
    }

    public static function linkedManagerId(?ClinicalStaff $staff): ?int
    {
        return self::linkedManager($staff)?->id;
    }

    private static function linkedManager(?ClinicalStaff $staff): ?Admin
    {
        if (! $staff) {
            return null;
        }

        if ($staff->admin_id) {
            $admin = $staff->relationLoaded('admin')
                ? $staff->admin
                : Admin::query()->find($staff->admin_id);
            if ($admin && $admin->isApproved() && AdminPermissions::isLeadership($admin)) {
                return $admin;
            }
        }

        $admin = Admin::query()
            ->whereRaw('LOWER(email) = ?', [strtolower((string) $staff->email)])
            ->first();

        if ($admin && $admin->isApproved() && AdminPermissions::isLeadership($admin)) {
            return $admin;
        }

        return null;
    }

    private static function aliasSpecialty(Admin $admin): string
    {
        if (AdminPermissions::isDeveloper($admin)) {
            return 'Developer';
        }

        return AdminPermissions::isCeo($admin) ? 'CEO' : 'Manager';
    }
}
