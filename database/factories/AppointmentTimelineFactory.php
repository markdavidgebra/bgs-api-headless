<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\AppointmentTimeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentTimeline>
 */
class AppointmentTimelineFactory extends Factory
{
    protected $model = AppointmentTimeline::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'event' => fake()->randomElement(['created', 'confirmed', 'rescheduled', 'completed', 'cancelled', 'note_added']),
            'description' => fake()->optional()->sentence(),
            'event_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'created_by' => fake()->optional(0.5)->randomElement([null, Admin::factory()]),
        ];
    }
}
