<?php

namespace Database\Factories;

use App\Models\Product;
use App\ProductType;
use Faker\Generator as Faker;
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
            'name' => fake()->words(3, true),
            'product_type' => fake()->randomElement(ProductType::cases()),
            'description' => fake()->paragraph(),
            // Keep images empty by default to avoid broken /storage paths in dev
            'images' => [],
            // Random list of features
            'features' => fake()->boolean(70) ? fake()->sentences(fake()->numberBetween(2, 5)) : [],
        ];
    }
}
