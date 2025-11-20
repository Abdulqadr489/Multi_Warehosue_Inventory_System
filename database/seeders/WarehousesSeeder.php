<?php

namespace Database\Seeders;

use App\Models\Countries\Country;
use App\Models\Warehouses\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehousesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $iraq = Country::where('code', 'IQ')->first();
        $us   = Country::where('code', 'US')->first();

        Warehouse::insert([
            [
                'name'       => 'Baghdad Central WH',
                'location'   => 'Baghdad',
                'country_id' => $iraq?->id,
            ],
            [
                'name'       => 'Erbil North WH',
                'location'   => 'Erbil',
                'country_id' => $iraq?->id,
            ],
            [
                'name'       => 'NY East WH',
                'location'   => 'New York',
                'country_id' => $us?->id,
            ],
        ]);
    }
}
