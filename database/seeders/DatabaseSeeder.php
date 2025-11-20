<?php

namespace Database\Seeders;

use Database\Factories\Countries\CountryFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountriesSeeder::class,
            WarehousesSeeder::class,
            ProductsSeeder::class,
            SuppliersSeeder::class,
            InventoriesSeeder::class,
        ]);


    }
}
