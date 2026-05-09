<?php

namespace Database\Factories;

use App\Models\MembershipPlan;
use App\Models\Patient;
use App\Models\PatientSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientSubscription>
 */
class PatientSubscriptionFactory extends Factory
{
    protected $model = PatientSubscription::class;

    public function definition(): array
    {
        $used = fake()->numberBetween(0, 5);
        $remaining = fake()->numberBetween(0, 15);

        return [
            'patient_id' => Patient::factory(),
            'membership_plan_id' => MembershipPlan::factory(),
            'start_date' => fake()->date(),
            'renewal_date' => fake()->optional()->date(),
            'end_date' => fake()->optional()->date(),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled', 'paused']),
            'sessions_used' => $used,
            'sessions_remaining' => $remaining,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
