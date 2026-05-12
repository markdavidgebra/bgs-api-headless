<?php

namespace App\Support;

use App\Models\Admin;
use Illuminate\Support\Collection;

class AdminNotificationRecipients
{
    /**
     * @return list<string>
     */
    public static function emailsForPermission(string $permission): array
    {
        return self::collectEmails(
            Admin::query()->orderBy('id')->get()->filter(
                static fn (Admin $admin): bool => AdminPermissions::canAccess($admin, $permission)
            )
        );
    }

    /**
     * @return list<string>
     */
    public static function superAdminEmails(): array
    {
        return self::collectEmails(
            Admin::query()->orderBy('id')->get()->filter(static function (Admin $admin): bool {
                $r = AdminPermissions::normalizeRole((string) ($admin->role ?? ''));

                return in_array($r, ['super admin', 'superadmin'], true);
            })
        );
    }

    /**
     * @param  Collection<int, Admin>  $admins
     * @return list<string>
     */
    private static function collectEmails(Collection $admins): array
    {
        return $admins
            ->pluck('email')
            ->map(static fn ($e) => strtolower(trim((string) $e)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
