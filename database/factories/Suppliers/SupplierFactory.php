<?php

namespace Database\Factories\Suppliers;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'         => $this->faker->company(),
            'contact_info' => $this->faker->phoneNumber(),
            'address'      => $this->faker->address(),
        ];
    }
}
