<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['in', 'out', 'adjustment']);

        return [
            'product_id' => Product::factory(),
            'type' => $type,
            'quantity' => fake()->numberBetween(1, 50),
            'reference' => fake()->optional()->bothify('REF-####'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
