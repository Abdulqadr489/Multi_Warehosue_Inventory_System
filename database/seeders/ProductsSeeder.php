<?php

namespace Database\Seeders;

use App\Models\Product\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::insert([
            [
                'name'        => 'Laptop Pro 15',
                'sku'         => 'LTP-15-PRO',
                'status'      => 'active',
                'description' => 'High-end laptop for professionals',
                'price'       => 1500.00,
            ],
            [
                'name'        => 'Wireless Mouse',
                'sku'         => 'MS-WL-01',
                'status'      => 'active',
                'description' => 'Ergonomic wireless mouse',
                'price'       => 25.00,
            ],
            [
                'name'        => 'Mechanical Keyboard',
                'sku'         => 'KB-MECH-01',
                'status'      => 'active',
                'description' => 'Backlit mechanical keyboard',
                'price'       => 80.00,
            ],
        ]);
    }
}
