<?php

namespace Tests\Unit\Warehouses;


use App\Models\Countries\Country;
use App\Models\User;
use App\Models\Warehouses\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_can_create_warehouse(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'name'       => 'Main Warehouse',
            'location'   => 'Baghdad',
            'country_id' => $country->id,
        ];

        $response = $this->postJson('/api/warehouses', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('warehouses', [
            'name'       => 'Main Warehouse',
            'location'   => 'Baghdad',
            'country_id' => $country->id,
        ]);
    }

    public function test_warehouse_index_returns_paginated_data(): void
    {
        $country = Country::factory()->create();

        Warehouse::factory()->create([
            'name'       => 'Warehouse A',
            'location'   => 'Baghdad',
            'country_id' => $country->id,
        ]);

        Warehouse::factory()->create([
            'name'       => 'Warehouse B',
            'location'   => 'Erbil',
            'country_id' => $country->id,
        ]);

        $response = $this->getJson('/api/warehouses');

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

    public function test_create_warehouse_requires_name_location_and_country(): void
    {
        $response = $this->postJson('/api/warehouses', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'location',
                'country_id',
            ]);
    }

    public function test_warehouse_must_be_linked_to_existing_country(): void
    {
        $payload = [
            'name'       => 'Test Warehouse',
            'location'   => 'Mosul',
            'country_id' => 999999, // non-existing
        ];

        $response = $this->postJson('/api/warehouses', $payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_unauthenticated_user_cannot_create_warehouse(): void
    {
        auth()->logout();

        $country = Country::factory()->create();

        $payload = [
            'name'       => 'Unauthorized Warehouse',
            'location'   => 'Kirkuk',
            'country_id' => $country->id,
        ];

        $response = $this->postJson('/api/warehouses', $payload);

        $response->assertStatus(401);
    }
}
