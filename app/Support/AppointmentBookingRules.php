<?php

namespace App\Support;

use App\Models\Admin;
use Illuminate\Support\Carbon;

final class AppointmentBookingRules
{
    /** ISO-8601 weekday: 7 = Sunday */
    public const CLOSED_WEEKDAY = 7;

    /** How many past calendar days admin/manager may book from the portal. */
    public const ADMIN_BACKDATE_DAYS = 5;

    public static function isClosedWeekday(Carbon|string|null $date): bool
    {
        if ($date === null || $date === '') {
            return false;
        }

        try {
            $weekday = (int) Carbon::parse($date)->format('N');
        } catch (\Throwable) {
            return true;
        }

        return $weekday === self::CLOSED_WEEKDAY;
    }

    public static function closedDateMessage(): string
    {
        return 'Appointments are not available on Sundays. Please choose another date.';
    }

    public static function canBackdate(?Admin $admin): bool
    {
        return $admin !== null && AdminPermissions::canAccess($admin, 'appointments.manage');
    }

    /**
     * Earliest calendar date for admin-portal booking (Y-m-d).
     * Applies to every signed-in admin, including custom front-desk roles.
     */
    public static function earliestBookableDate(?Admin $admin): string
    {
        $today = Carbon::today();
        if ($admin === null) {
            return $today->toDateString();
        }

        return $today->copy()->subDays(self::ADMIN_BACKDATE_DAYS)->toDateString();
    }
}
