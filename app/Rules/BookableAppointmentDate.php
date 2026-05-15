<?php

namespace App\Rules;

use App\Support\AppointmentBookingRules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BookableAppointmentDate implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (AppointmentBookingRules::isClosedWeekday(is_string($value) ? $value : null)) {
            $fail(AppointmentBookingRules::closedDateMessage());
        }
    }
}
