<?php

namespace Database\Factories\Inventories;

use App\Models\Product\Product;
use App\Models\Warehouses\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id'       => Product::factory(),
            'warehouse_id'     => Warehouse::factory(),
            'quantity'         => $this->faker->numberBetween(0, 100),
            'minimum_quantity' => $this->faker->numberBetween(0, 10),
        ];
    }
}
