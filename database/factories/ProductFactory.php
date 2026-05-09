<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Product';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('##'),
            'category_id' => fake()->boolean(70) ? ProductCategory::factory() : null,
            'brand' => fake()->optional()->company(),
            'sku' => 'SKU-'.fake()->unique()->numerify('########'),
            'description' => fake()->optional()->paragraph(),
            'image' => null,
            'cost_price' => fake()->randomFloat(2, 50, 2000),
            'selling_price' => fake()->randomFloat(2, 100, 5000),
            'discount_price' => fake()->optional()->randomFloat(2, 80, 4000),
            'stock_quantity' => fake()->numberBetween(0, 500),
            'minimum_stock_alert' => fake()->numberBetween(0, 20),
            'unit' => fake()->randomElement(['pc', 'box', 'bottle', 'set']),
            'status' => 'active',
            'is_available_for_sale' => true,
            'expiry_date' => fake()->optional()->date(),
            'batch_number' => fake()->optional()->bothify('BATCH-####'),
            'supplier' => fake()->optional()->company(),
        ];
    }
}
