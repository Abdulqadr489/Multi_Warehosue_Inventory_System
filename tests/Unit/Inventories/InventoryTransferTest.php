<?php

namespace Tests\Unit\Inventories;

use App\Models\Inventories\Inventory;
use App\Models\Product\Product;
use App\Models\User;
use App\Models\Warehouses\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTransferTest extends TestCase
{

    use RefreshDatabase;

    protected User $user;
    protected Warehouse $sourceWarehouse;
    protected Warehouse $targetWarehouse;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Authenticated API user
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');

        // Two warehouses + one product via factories
        $this->sourceWarehouse = Warehouse::factory()->create();
        $this->targetWarehouse = Warehouse::factory()->create();
        $this->product         = Product::factory()->create(['status' => 'active']);
    }

    /**
     * Central place to call your transfer endpoint.
     * Adjust the URL or route() name to match your app.
     */
    protected function postTransfer(array $payload)
    {
        // If you use apiResource('inventory-transfers', ...):
        // return $this->postJson(route('inventory-transfers.store'), $payload);

        // If your route is Route::post('inventory_transfers', ...):
        return $this->postJson('/api/inventory_transfer', $payload);

        // Or use '/api/inventory-transfers' if that's your URI.
    }

    public function test_transfer_moves_stock_between_warehouses(): void
    {
        // ARRANGE
        $sourceInventory = Inventory::factory()
            ->for($this->product)
            ->for($this->sourceWarehouse)
            ->create([
                'quantity'         => 20,
                'minimum_quantity' => 5,
            ]);

        $targetInventory = Inventory::factory()
            ->for($this->product)
            ->for($this->targetWarehouse)
            ->create([
                'quantity'         => 5,
                'minimum_quantity' => 2,
            ]);

        $payload = [
            'product_id'        => $this->product->id,
            'from_warehouse_id' => $this->sourceWarehouse->id,
            'to_warehouse_id'   => $this->targetWarehouse->id,
            'quantity'          => 7,
            'date'              => now()->toISOString(),
            'minimum_quantity'  => 5,
        ];

        // ACT
        $response = $this->postTransfer($payload);

        // ASSERT (response)
        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // ASSERT (DB – quantities updated)
        $sourceInventory->refresh();
        $targetInventory->refresh();

        $this->assertEquals(
            13,
            (float) $sourceInventory->quantity,
            'Source warehouse quantity should decrease by 7.'
        );

        $this->assertEquals(
            12,
            (float) $targetInventory->quantity,
            'Target warehouse quantity should increase by 7.'
        );
    }

    public function test_transfer_creates_target_inventory_if_missing(): void
    {
        // ARRANGE – source has stock, target has NO inventory row
        $sourceInventory = Inventory::factory()
            ->for($this->product)
            ->for($this->sourceWarehouse)
            ->create([
                'quantity'         => 15,
                'minimum_quantity' => 3,
            ]);

        $this->assertDatabaseMissing('inventories', [
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->targetWarehouse->id,
        ]);

        $payload = [
            'product_id'        => $this->product->id,
            'from_warehouse_id' => $this->sourceWarehouse->id,
            'to_warehouse_id'   => $this->targetWarehouse->id,
            'quantity'          => 5,
            'date'              => now()->toISOString(),
            'minimum_quantity'  => 3,
        ];

        // ACT
        $response = $this->postTransfer($payload);

        // ASSERT (response)
        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // ASSERT (new target inventory created)
        $this->assertDatabaseHas('inventories', [
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->targetWarehouse->id,
            'quantity'     => 5,
        ]);

        // ASSERT (source decreased)
        $sourceInventory->refresh();
        $this->assertEquals(
            10,
            (float) $sourceInventory->quantity,
            'Source warehouse quantity should decrease by 5.'
        );
    }

    public function test_transfer_fails_when_insufficient_stock_in_source(): void
    {
        // ARRANGE – source has low stock
        $sourceInventory = Inventory::factory()
            ->for($this->product)
            ->for($this->sourceWarehouse)
            ->create([
                'quantity'         => 3,
                'minimum_quantity' => 1,
            ]);

        $payload = [
            'product_id'        => $this->product->id,
            'from_warehouse_id' => $this->sourceWarehouse->id,
            'to_warehouse_id'   => $this->targetWarehouse->id,
            'quantity'          => 10,  // too much
            'date'              => now()->toISOString(),
            'minimum_quantity'  => 1,
        ];

        // ACT
        $response = $this->postTransfer($payload);

        // ASSERT (response – matches your actual API)
        $response
            ->assertStatus(500)                // your ApiResponse is using 500 here
            ->assertJsonFragment([
                'success' => false,
            ])
            ->assertJsonFragment([
                'errors' => 'Insufficient stock in this warehouse.',
            ]);

        // ASSERT (DB – source unchanged)
        $sourceInventory->refresh();
        $this->assertEquals(
            3,
            (float) $sourceInventory->quantity,
            'Source warehouse quantity should remain unchanged when transfer fails.'
        );
    }
    public function test_transfer_rejects_same_from_and_to_warehouse(): void
    {
        // ARRANGE – from & to are the same
        $payload = [
            'product_id'        => $this->product->id,
            'from_warehouse_id' => $this->sourceWarehouse->id,
            'to_warehouse_id'   => $this->sourceWarehouse->id,
            'quantity'          => 5,
            'date'              => now()->toISOString(),
            'minimum_quantity'  => 5,
        ];

        // ACT
        $response = $this->postTransfer($payload);

        // ASSERT – adjust depending on how you validate this rule
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['to_warehouse_id']);
    }

    public function test_transfer_validates_required_fields(): void
    {
        // ARRANGE – empty payload
        $payload = [];


        $response = $this->postTransfer($payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'product_id',
                'from_warehouse_id',
                'to_warehouse_id',
                'quantity',
                'date'
            ]);
    }
}
