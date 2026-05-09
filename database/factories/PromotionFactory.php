<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Promo',
            'code' => fake()->boolean(70) ? fake()->unique()->bothify('PROMO-????') : null,
            'type' => fake()->randomElement(['seasonal', 'flash', 'loyalty']),
            'status' => fake()->randomElement(['draft', 'active', 'expired']),
            'description' => fake()->paragraph(),
            'image' => null,
            'discount_value' => fake()->randomFloat(2, 5, 5000),
            'discount_method' => fake()->randomElement(['percentage', 'fixed', 'free_service', 'bundle']),
            'minimum_spend' => fake()->optional()->randomFloat(2, 0, 5000),
            'maximum_discount' => fake()->optional()->randomFloat(2, 100, 10000),
            'applies_to' => fake()->randomElement(['services', 'packages', 'memberships', 'products', 'all']),
            'start_date' => $start = fake()->date(),
            'end_date' => fake()->dateTimeBetween($start, '+3 months')->format('Y-m-d'),
            'time_limit' => null,
            'available_days' => fake()->optional(0.5)->randomElement([['mon', 'wed', 'fri'], ['sat', 'sun']]),
            'usage_limit' => fake()->optional()->numberBetween(10, 1000),
            'limit_per_patient' => fake()->optional()->numberBetween(1, 5),
            'new_patients_only' => fake()->boolean(20),
            'can_combine_with_other_promos' => fake()->boolean(30),
            'terms_and_conditions' => fake()->optional()->paragraph(),
            'internal_notes' => null,
            'display_note' => fake()->optional()->sentence(),
        ];
    }
}
