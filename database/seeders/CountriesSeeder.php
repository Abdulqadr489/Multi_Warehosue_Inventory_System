<?php

namespace Database\Seeders;

use App\Models\Countries\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        Country::insert([
            [
                'name' => 'Iraq',
                'code' => 'IQ',
            ],
            [
                'name' => 'United States',
                'code' => 'US',
            ],
            [
                'name' => 'United Kingdom',
                'code' => 'GB',
            ],
        ]);
    }
}
