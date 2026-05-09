<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorWeeklySchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorWeeklySchedule>
 */
class DoctorWeeklyScheduleFactory extends Factory
{
    protected $model = DoctorWeeklySchedule::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
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
