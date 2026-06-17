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
        $faker = \Faker\Factory::create();
        $name = $faker->unique()->words(3, true).' Service';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$faker->unique()->numerify('###'),
            'short_description' => $faker->sentence(),
            'description' => $faker->paragraphs(2, true),
            'price' => $faker->randomFloat(2, 500, 15000),
            'promo_price' => $faker->optional(0.3)->randomFloat(2, 300, 12000),
            'duration_minutes' => $faker->randomElement([30, 45, 60, 90, 120]),
            'session_count' => $faker->numberBetween(1, 10),
            'icon_class' => 'fas fa-spa',
            'image' => null,
            'recovery_time' => $faker->randomElement(['None', '24h', '48h', '1 week']),
            'max_appointments_per_day' => $faker->optional()->numberBetween(1, 20),
            'status' => 'active',
            'is_featured' => $faker->boolean(20),
            'is_bookable' => true,
            'before_care' => $faker->optional()->paragraph(),
            'after_care' => $faker->optional()->paragraph(),
            'notes' => null,
        ];
    }
}
