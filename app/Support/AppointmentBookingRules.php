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
        return AdminPermissions::isFullAccess($admin);
    }

    /**
     * Earliest calendar date the given admin may book (Y-m-d).
     */
    public static function earliestBookableDate(?Admin $admin): string
    {
        $today = Carbon::today();
        if (self::canBackdate($admin)) {
            return $today->copy()->subDays(self::ADMIN_BACKDATE_DAYS)->toDateString();
        }

        return $today->toDateString();
    }
}
