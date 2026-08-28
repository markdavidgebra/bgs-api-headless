<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The `doctor` guard was renamed to `clinical_staff` to free the name for the new
 * Doctor portal. Portal permission keys are persisted as JSON on the role tables,
 * so the stored values have to move with the code constants — otherwise every
 * role silently loses access the moment the new keys go live.
 */
return new class extends Migration
{
    /** Clinical staff portal keys: `doctor.x` -> `clinical_staff.x`. */
    private const STAFF_KEYS = [
        'dashboard',
        'appointments',
        'patient_records',
        'treatment_notes',
        'products',
        'services',
        'availability',
        'notifications',
        'profile',
    ];

    public function up(): void
    {
        $this->rewriteStaffRoles(fn (string $key): string => str_starts_with($key, 'doctor.')
            ? 'clinical_staff.'.substr($key, strlen('doctor.'))
            : $key);

        $this->rewriteAdminRoles('doctors.manage', 'clinical_staff.manage');
    }

    public function down(): void
    {
        $this->rewriteStaffRoles(fn (string $key): string => str_starts_with($key, 'clinical_staff.')
            ? 'doctor.'.substr($key, strlen('clinical_staff.'))
            : $key);

        $this->rewriteAdminRoles('clinical_staff.manage', 'doctors.manage');
    }

    /**
     * @param  callable(string): string  $map
     */
    private function rewriteStaffRoles(callable $map): void
    {
        $table = Schema::hasTable('clinical_staff_roles')
            ? 'clinical_staff_roles'
            : (Schema::hasTable('doctor_roles') ? 'doctor_roles' : null);

        if ($table === null) {
            return;
        }

        foreach (DB::table($table)->select('id', 'permissions')->get() as $role) {
            $permissions = $this->decode($role->permissions);
            if ($permissions === null) {
                continue;
            }

            $updated = array_values(array_unique(array_map(
                static fn ($key): string => $map((string) $key),
                $permissions,
            )));

            if ($updated !== $permissions) {
                DB::table($table)->where('id', $role->id)->update([
                    'permissions' => json_encode($updated),
                ]);
            }
        }
    }

    private function rewriteAdminRoles(string $from, string $to): void
    {
        if (! Schema::hasTable('admin_roles')) {
            return;
        }

        foreach (DB::table('admin_roles')->select('id', 'permissions')->get() as $role) {
            $permissions = $this->decode($role->permissions);
            if ($permissions === null || ! in_array($from, $permissions, true)) {
                continue;
            }

            $updated = array_values(array_unique(array_map(
                static fn ($key): string => (string) $key === $from ? $to : (string) $key,
                $permissions,
            )));

            DB::table('admin_roles')->where('id', $role->id)->update([
                'permissions' => json_encode($updated),
            ]);
        }
    }

    /**
     * @return list<string>|null
     */
    private function decode(mixed $raw): ?array
    {
        if (is_array($raw)) {
            return array_values(array_map('strval', $raw));
        }

        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_values(array_map('strval', $decoded)) : null;
    }
};
