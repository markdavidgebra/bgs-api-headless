<?php

namespace Database\Factories;

use App\Models\ClinicalStaff;
use App\Models\ClinicalStaffBlockedDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicalStaffBlockedDate>
 */
class ClinicalStaffBlockedDateFactory extends Factory
{
    protected $model = ClinicalStaffBlockedDate::class;

    public function definition(): array
    {
        return [
            'clinical_staff_id' => ClinicalStaff::factory(),
            'blocked_date' => fake()->dateTimeBetween('+1 day', '+4 months')->format('Y-m-d'),
            'reason' => fake()->optional()->sentence(),
        ];
    }
}
