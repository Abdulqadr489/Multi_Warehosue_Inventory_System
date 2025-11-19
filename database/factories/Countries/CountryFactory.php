<?php

namespace Database\Factories\Countries;

use App\Models\Countries\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->country(),
            'code' => $this->faker->countryCode(),
        ];
    }
}
