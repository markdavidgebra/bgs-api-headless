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
            'patient_concern' => $this->faker->optional()->paragraph(),
            'appointment_remarks' => $this->faker->optional()->sentence(),
            'admin_notes' => $this->faker->optional()->sentence(),
            'doctor_notes' => $this->faker->optional()->paragraph(),
            'instructions' => $this->faker->optional()->sentence(),
            'alerts' => $this->faker->optional()->sentence(),
        ];
    }
}
