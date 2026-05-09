<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true).' Service';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'price' => fake()->randomFloat(2, 500, 15000),
            'promo_price' => fake()->optional(0.3)->randomFloat(2, 300, 12000),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90, 120]),
            'session_count' => fake()->numberBetween(1, 10),
            'icon_class' => 'fas fa-spa',
            'image' => null,
            'recovery_time' => fake()->randomElement(['None', '24h', '48h', '1 week']),
            'max_appointments_per_day' => fake()->optional()->numberBetween(1, 20),
            'status' => 'active',
            'is_featured' => fake()->boolean(20),
            'is_bookable' => true,
            'before_care' => fake()->optional()->paragraph(),
            'after_care' => fake()->optional()->paragraph(),
            'notes' => null,
        ];
    }
}
