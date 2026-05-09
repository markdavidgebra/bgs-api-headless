<?php

namespace Database\Factories;

use App\Models\TreatmentPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TreatmentPackage>
 */
class TreatmentPackageFactory extends Factory
{
    protected $model = TreatmentPackage::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Package';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('##'),
            'category' => fake()->randomElement(['Facial', 'Body', 'Laser', 'Wellness']),
            'description' => fake()->paragraph(),
            'image' => null,
            'status' => 'active',
            'price' => fake()->randomFloat(2, 2000, 50000),
            'original_price' => fake()->optional()->randomFloat(2, 2500, 60000),
            'discount_percent' => fake()->optional()->randomFloat(2, 5, 40),
            'validity_value' => fake()->numberBetween(1, 12),
            'validity_type' => fake()->randomElement(['days', 'months', 'years']),
            'expiry_rule' => fake()->randomElement(['starts_after_purchase', 'starts_after_first_use']),
            'max_usage_per_day' => fake()->optional()->numberBetween(1, 3),
            'allow_sharing' => fake()->boolean(15),
            'is_refundable' => fake()->boolean(25),
            'before_care' => fake()->optional()->sentence(),
            'aftercare' => fake()->optional()->sentence(),
            'internal_notes' => null,
        ];
    }
}
