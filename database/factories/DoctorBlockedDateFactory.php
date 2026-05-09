<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorBlockedDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorBlockedDate>
 */
class DoctorBlockedDateFactory extends Factory
{
    protected $model = DoctorBlockedDate::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'blocked_date' => fake()->dateTimeBetween('+1 day', '+4 months')->format('Y-m-d'),
            'reason' => fake()->optional()->sentence(),
        ];
    }
}
