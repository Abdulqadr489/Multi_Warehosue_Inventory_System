<?php

namespace Database\Seeders;

use App\Models\Suppliers\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::insert([
            [
                'name'         => 'Baghdad Electronics',
                'contact_info' => 'baghdad.elec@example.com / +964-770-0000000',
                'address'      => 'Baghdad, Iraq',
            ],
            [
                'name'         => 'Global Tech Distribution',
                'contact_info' => 'global.tech@example.com / +1-555-0000',
                'address'      => 'New York, USA',
            ],
        ]);
    }
}
