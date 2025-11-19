<?php

namespace Database\Factories\Warehouses;

use App\Models\Countries\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => 'WH '.$this->faker->city(),
            'location'   => $this->faker->city(),
            'country_id' => Country::factory(),
        ];
    }
}
