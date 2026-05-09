<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentNote>
 */
class AppointmentNoteFactory extends Factory
{
    protected $model = AppointmentNote::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'patient_concern' => fake()->optional()->paragraph(),
            'appointment_remarks' => fake()->optional()->sentence(),
            'admin_notes' => fake()->optional()->sentence(),
            'doctor_notes' => fake()->optional()->paragraph(),
            'instructions' => fake()->optional()->sentence(),
            'alerts' => fake()->optional()->sentence(),
        ];
    }
}
