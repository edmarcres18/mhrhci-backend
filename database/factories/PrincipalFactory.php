<?php

namespace Database\Factories;

use App\Models\Principal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Principal>
 */
class PrincipalFactory extends Factory
{
    protected $model = Principal::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company.' Medical',
            'logo' => null, // Keep null by default to avoid storage dependency
            'description' => $this->faker->sentence(12),
        ];
    }
}
