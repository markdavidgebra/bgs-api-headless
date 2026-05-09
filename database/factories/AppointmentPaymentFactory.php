<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentPayment>
 */
class AppointmentPaymentFactory extends Factory
{
    protected $model = AppointmentPayment::class;

    public function definition(): array
    {
        $isPaid = fake()->boolean(70);

        return [
            'appointment_id' => Appointment::factory(),
            'invoice_no' => 'INV-'.fake()->unique()->numerify('########'),
            'amount' => fake()->randomFloat(2, 500, 25000),
            'payment_method' => fake()->randomElement(['cash', 'gcash', 'card', 'bank_transfer']),
            'payment_status' => $isPaid ? 'paid' : fake()->randomElement(['pending', 'partial']),
            'is_paid' => $isPaid,
            'deposit_notes' => fake()->optional()->sentence(),
            'reference_no' => fake()->optional()->bothify('REF-####??'),
            'paid_at' => $isPaid ? fake()->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
