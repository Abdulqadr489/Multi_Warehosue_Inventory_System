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

class InventoryTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected Product $product;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');

        $this->warehouse = Warehouse::factory()->create();
        $this->product   = Product::factory()->create([
            'status' => 'active',
        ]);
        $this->supplier  = Supplier::factory()->create();
    }


    protected function postTransaction(array $payload)
    {
        return $this->postJson('/api/inventory_transactions', $payload);
    }

    public function test_in_transaction_increases_existing_inventory_quantity(): void
    {
        $inventory = Inventory::factory()
            ->for($this->product)
            ->for($this->warehouse)
            ->create([
                'quantity'         => 10,
                'minimum_quantity' => 5,
            ]);

        $payload = [
            'product_id'       => $this->product->id,
            'warehouse_id'     => $this->warehouse->id,
            'supplier_id'      => $this->supplier->id,
            'quantity'         => 5,
            'transaction_type' => 'IN',
            'date'             => now()->toISOString(),
            'minimum_quantity' => 5,
        ];


        $response = $this->postTransaction($payload);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $inventory->refresh();
        $this->assertEquals(
            15,
            (float) $inventory->quantity,
            'Inventory quantity should increase by 5 after IN transaction.'
        );
    }

    public function test_in_transaction_creates_inventory_if_not_exists(): void
    {
        $this->assertDatabaseMissing('inventories', [
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
        ]);

        $payload = [
            'product_id'       => $this->product->id,
            'warehouse_id'     => $this->warehouse->id,
            'supplier_id'      => $this->supplier->id,
            'quantity'         => 7,
            'transaction_type' => 'IN',
            'date'             => now()->toISOString(),
            'minimum_quantity' => 5,

        ];

        $response = $this->postTransaction($payload);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('inventories', [
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity'     => 7,
        ]);
    }

    public function test_out_transaction_decreases_inventory_when_enough_stock(): void
    {
        $inventory = Inventory::factory()
            ->for($this->product)
            ->for($this->warehouse)
            ->create([
                'quantity'         => 20,
                'minimum_quantity' => 5,
            ]);

        $payload = [
            'product_id'       => $this->product->id,
            'warehouse_id'     => $this->warehouse->id,
            'supplier_id'      => $this->supplier->id,
            'quantity'         => 8,
            'transaction_type' => 'OUT',
            'date'             => now()->toISOString(),
            'minimum_quantity' => 5,

        ];

        $response = $this->postTransaction($payload);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $inventory->refresh();
        $this->assertEquals(
            12,
            (float) $inventory->quantity,
            'Inventory quantity should decrease by 8 after OUT transaction.'
        );
    }

    public function test_out_transaction_fails_when_insufficient_stock(): void
    {
        $inventory = Inventory::factory()
            ->for($this->product)
            ->for($this->warehouse)
            ->create([
                'quantity'         => 3,
                'minimum_quantity' => 1,
            ]);

        $payload = [
            'product_id'       => $this->product->id,
            'warehouse_id'     => $this->warehouse->id,
            'supplier_id'      => $this->supplier->id,
            'quantity'         => 10,
            'transaction_type' => 'OUT',
            'date'             => now()->toISOString(),
            'minimum_quantity' => 5,

        ];

        $response = $this->postTransaction($payload);

        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Insufficient stock in this warehouse.',
            ]);

        $inventory->refresh();
        $this->assertEquals(
            3,
            (float) $inventory->quantity,
            'Inventory quantity should remain unchanged when OUT fails.'
        );
    }

    public function test_transaction_rejects_invalid_transaction_type(): void
    {
        $payload = [
            'product_id'       => $this->product->id,
            'warehouse_id'     => $this->warehouse->id,
            'supplier_id'      => $this->supplier->id,
            'quantity'         => 5,
            'transaction_type' => 'INVALID',   // not IN or OUT
            'date'             => now()->toISOString(),
            'minimum_quantity' => 5,
        ];

        $response = $this->postTransaction($payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['transaction_type']);
    }

    public function test_transaction_validates_required_fields(): void
    {
        $payload = [];

        $response = $this->postTransaction($payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'product_id',
                'warehouse_id',
                'quantity',
                'transaction_type',
            ]);
    }

}
