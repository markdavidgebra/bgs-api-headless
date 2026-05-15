<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class AppointmentBookingRules
{
    /** ISO-8601 weekday: 7 = Sunday */
    public const CLOSED_WEEKDAY = 7;

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
}
