<?php

namespace Database\Factories;

use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    protected $model = MembershipPlan::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Plan';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('##'),
            'type' => fake()->randomElement(['standard', 'premium', 'vip']),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 999, 25000),
            'status' => 'active',
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
            'duration_value' => fake()->numberBetween(1, 12),
            'duration_type' => fake()->randomElement(['month', 'year']),
            'max_usage_per_month' => fake()->optional()->numberBetween(2, 20),
            'rollover_unused_sessions' => fake()->boolean(30),
            'cancellation_allowed' => fake()->boolean(50),
            'pause_allowed' => fake()->boolean(40),
            'terms_and_conditions' => fake()->optional()->paragraph(),
            'before_care' => fake()->optional()->sentence(),
            'aftercare' => fake()->optional()->sentence(),
            'internal_notes' => null,
        ];
    }
}
