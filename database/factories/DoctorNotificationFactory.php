<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorNotification>
 */
class DoctorNotificationFactory extends Factory
{
    protected $model = DoctorNotification::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'type' => fake()->randomElement(DoctorNotification::TYPES),
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'read_at' => fake()->optional(0.35)->dateTimeBetween('-1 week', 'now'),
            'appointment_id' => null,
            'patient_id' => null,
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }
}
