<?php

namespace Database\Factories;

use App\Models\ClinicalStaff;
use App\Models\ClinicalStaffWeeklySchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicalStaffWeeklySchedule>
 */
class ClinicalStaffWeeklyScheduleFactory extends Factory
{
    protected $model = ClinicalStaffWeeklySchedule::class;

    public function definition(): array
    {
        return [
            'doctor_id' => ClinicalStaff::factory(),
            'weekday' => fake()->numberBetween(1, 7),
            'is_active' => true,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'start_time' => null,
            'end_time' => null,
        ]);
    }
}
