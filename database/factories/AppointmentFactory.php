<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'appointment_no' => 'APT-'.fake()->unique()->numerify('########'),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'service_id' => Service::factory(),
            'appointment_date' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'appointment_time' => fake()->time('H:i:s'),
            'status' => fake()->randomElement(['pending', 'confirmed', 'completed', 'cancelled', 'rescheduled']),
            'created_by' => fake()->boolean(40) ? Admin::factory() : null,
            'updated_by' => null,
        ];
    }
}
