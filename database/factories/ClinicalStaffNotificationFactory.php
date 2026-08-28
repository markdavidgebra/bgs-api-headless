<?php

namespace Database\Factories;

use App\Models\ClinicalStaff;
use App\Models\ClinicalStaffNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicalStaffNotification>
 */
class ClinicalStaffNotificationFactory extends Factory
{
    protected $model = ClinicalStaffNotification::class;

    public function definition(): array
    {
        return [
            'clinical_staff_id' => ClinicalStaff::factory(),
            'type' => fake()->randomElement(ClinicalStaffNotification::TYPES),
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
