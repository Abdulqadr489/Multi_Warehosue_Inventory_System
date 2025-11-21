<?php

namespace Tests\Unit\Suppliers;

use App\Models\Suppliers\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_can_create_supplier(): void
    {
        $payload = [
            'name'         => 'Test Supplier',
            'contact_info' => 'supplier@example.com',
            'address'      => 'Baghdad, Iraq',
        ];

        $response = $this->postJson('/api/suppliers', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('suppliers', [
            'name'         => 'Test Supplier',
            'contact_info' => 'supplier@example.com',
        ]);
    }

    public function test_supplier_index_returns_paginated_data(): void
    {
        Supplier::factory()->create([
            'name' => 'Supplier A',
        ]);

        Supplier::factory()->create([
            'name' => 'Supplier B',
        ]);

        $response = $this->getJson('/api/suppliers');

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

    public function test_create_supplier_requires_name(): void
    {
        $response = $this->postJson('/api/suppliers', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
            ]);
    }

    public function test_unauthenticated_user_cannot_create_supplier(): void
    {
        auth()->logout();

        $payload = [
            'name'         => 'Unauthorized Supplier',
            'contact_info' => 'nope@example.com',
            'address'      => 'Somewhere',
        ];

        $response = $this->postJson('/api/suppliers', $payload);

        $response->assertStatus(401);
    }
}
