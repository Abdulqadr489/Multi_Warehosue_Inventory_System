<?php

namespace Tests\Unit\Inventories;

use App\Models\Countries\Country;
use App\Models\Inventories\Inventory;
use App\Models\Product\Product;
use App\Models\Suppliers\Supplier;
use App\Models\User;
use App\Models\Warehouses\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }
    protected function createLowStockScenario(): array
    {
        $country = Country::factory()->create([
            'name' => 'LowStockLand',
            'code' => 'LS',
        ]);

        $warehouse = Warehouse::factory()->create([
            'name'       => 'WH Low',
            'location'   => 'City LS',
            'country_id' => $country->id,
        ]);

        $product = Product::factory()->create([
            'name'        => 'Low Product',
            'sku'         => 'SKU_LOW',
            'status'      => 'active',
            'description' => 'Low stock product',
            'price'       => 5,
        ]);

        $supplier = Supplier::factory()->create([
            'name'         => 'Supplier LS',
            'contact_info' => '999999',
            'address'      => 'Somewhere',
        ]);

        $inventory = Inventory::factory()
            ->for($product)
            ->for($warehouse)
            ->create([
                'quantity'         => 3,
                'minimum_quantity' => 3,
            ]);

        return [$inventory, $product, $warehouse, $country, $supplier];
    }

    public function test_low_stock_items_appear_in_low_stock_report_endpoint(): void
    {
        [$inventory, $product, $warehouse, $country, $supplier] = $this->createLowStockScenario();

        $response = $this->getJson('/api/reports/low_stock');

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');

        $this->assertIsArray($data, 'Low stock report data should be an array');
        $this->assertNotEmpty($data, 'Low stock report should contain at least one item');

        $row = collect($data)->firstWhere('sku', $product->sku) ?? $data[0];

        $this->assertArrayHasKey('product_name', $row);
        $this->assertArrayHasKey('sku', $row);
        $this->assertArrayHasKey('current_quantity', $row);
        $this->assertArrayHasKey('minimum_required', $row);
        $this->assertArrayHasKey('warehouse_name', $row);
        $this->assertArrayHasKey('warehouse_location', $row);
        $this->assertArrayHasKey('country', $row);
        $this->assertArrayHasKey('supplier_name', $row);
        $this->assertArrayHasKey('supplier_contact_info', $row);


        $this->assertEquals($product->name, $row['product_name']);
        $this->assertEquals($product->sku, $row['sku']);
        $this->assertEquals($inventory->quantity, $row['current_quantity']);
        $this->assertEquals($inventory->minimum_quantity, $row['minimum_required']);
        $this->assertEquals($warehouse->name, $row['warehouse_name']);
        $this->assertEquals($warehouse->location, $row['warehouse_location']);
        $this->assertEquals($country->name, $row['country']);
    }
}
