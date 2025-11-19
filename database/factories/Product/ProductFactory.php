<?php

namespace Database\Factories\Product;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => $this->faker->words(3, true),
            'sku'         => strtoupper($this->faker->unique()->bothify('SKU-####')),
            'status'      => 'active',
            'description' => $this->faker->sentence(),
            'price'       => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
