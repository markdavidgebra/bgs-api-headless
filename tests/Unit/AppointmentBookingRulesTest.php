<?php

namespace Tests\Unit;

use App\Support\AppointmentBookingRules;
use PHPUnit\Framework\TestCase;

class AppointmentBookingRulesTest extends TestCase
{
    public function test_sunday_is_closed(): void
    {
        $this->assertTrue(AppointmentBookingRules::isClosedWeekday('2026-05-10'));
    }

    public function test_weekday_is_not_closed(): void
    {
        $this->assertFalse(AppointmentBookingRules::isClosedWeekday('2026-05-11'));
    }
}
