<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $patient = Patient::factory();

        return [
            'payment_id' => fake()->unique()->regexify('PAY-[0-9]{6}'),
            'patient_id' => $patient,
            'reference_type' => fake()->randomElement(['appointment', 'package', 'membership', 'product']),
            'reference_id' => null,
            'amount' => fake()->randomFloat(2, 100, 20000),
            'payment_method' => fake()->randomElement(['cash', 'gcash', 'card', 'bank_transfer']),
            'payment_status' => fake()->randomElement(['paid', 'unpaid', 'partial', 'refunded']),
            'payment_date' => fake()->date(),
            'transaction_reference' => fake()->optional()->bothify('TXN-########'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forAppointment(?Appointment $appointment = null): static
    {
        return $this->state(function (array $attributes) use ($appointment) {
            if ($appointment !== null) {
                return [
                    'reference_type' => 'appointment',
                    'reference_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                ];
            }

            $patient = Patient::factory();

            return [
                'reference_type' => 'appointment',
                'reference_id' => Appointment::factory()->for($patient, 'patient'),
                'patient_id' => $patient,
            ];
        });
    }
}
