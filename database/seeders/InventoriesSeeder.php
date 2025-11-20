<?php

namespace Database\Seeders;

use App\Models\Inventories\Inventory;
use App\Models\Product\Product;
use App\Models\Warehouses\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baghdadWh = Warehouse::where('name', 'Baghdad Central WH')->first();
        $erbilWh   = Warehouse::where('name', 'Erbil North WH')->first();
        $nyWh      = Warehouse::where('name', 'NY East WH')->first();

        $laptop    = Product::where('sku', 'LTP-15-PRO')->first();
        $mouse     = Product::where('sku', 'MS-WL-01')->first();
        $keyboard  = Product::where('sku', 'KB-MECH-01')->first();

        Inventory::insert([
            [
                'product_id'       => $laptop?->id,
                'warehouse_id'     => $baghdadWh?->id,
                'quantity'         => 20,
                'minimum_quantity' => 5,
            ],
            [
                'product_id'       => $mouse?->id,
                'warehouse_id'     => $erbilWh?->id,
                'quantity'         => 50,
                'minimum_quantity' => 10,
            ],

            // Low stock example
            [
                'product_id'       => $keyboard?->id,
                'warehouse_id'     => $nyWh?->id,
                'quantity'         => 3,   // below minimum
                'minimum_quantity' => 10,
            ],
        ]);
    }
}
