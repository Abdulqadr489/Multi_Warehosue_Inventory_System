<?php

namespace Tests\Unit\Products;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_can_create_product(): void
    {
        $payload = [
            'name'        => 'Test Product',
            'sku'         => 'SKU-123',
            'status'      => 'active',
            'description' => 'Sample product description',
            'price'       => 100.50,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name'  => 'Test Product',
            'sku'   => 'SKU-123',
            'price' => 100.50,
        ]);
    }

    public function test_product_index_returns_paginated_data(): void
    {
        Product::factory()->create([
            'name' => 'Product A',
            'sku'  => 'SKU-A',
        ]);

        Product::factory()->create([
            'name' => 'Product B',
            'sku'  => 'SKU-B',
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertGreaterThanOrEqual(2, $response->json('data.total'));
    }

    public function test_create_product_requires_name_sku_and_price(): void
    {
        $response = $this->postJson('/api/products', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'sku',
                'price',
            ]);
    }

    public function test_cannot_create_product_with_duplicate_sku(): void
    {
        Product::factory()->create([
            'name' => 'Original Product',
            'sku'  => 'SKU-123',
        ]);

        $payload = [
            'name'        => 'Duplicate Product',
            'sku'         => 'SKU-123',
            'status'      => 'active',
            'description' => 'Duplicate SKU product',
            'price'       => 200,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);

        $this->assertEquals(
            1,
            Product::where('sku', 'SKU-123')->count(),
            'There should only be one product with SKU-123'
        );
    }

    public function test_unauthenticated_user_cannot_create_product(): void
    {
        auth()->logout();

        $payload = [
            'name'        => 'Unauthorized Product',
            'sku'         => 'SKU-UNAUTH',
            'status'      => 'active',
            'description' => 'Should not be created',
            'price'       => 50,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(401);
    }
}
