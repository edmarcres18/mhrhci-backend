<?php

namespace Database\Factories;

use App\Models\Product;
use App\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'product_type' => $this->faker->randomElement(ProductType::cases()),
            'description' => $this->faker->paragraph(),
            // Keep images empty by default to avoid broken /storage paths in dev
            'images' => [],
            // Random list of features
            'features' => $this->faker->boolean(70) ? $this->faker->sentences($this->faker->numberBetween(2, 5)) : [],
        ];
    }
}
