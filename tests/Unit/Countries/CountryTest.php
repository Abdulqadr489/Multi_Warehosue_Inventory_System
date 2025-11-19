<?php

namespace Tests\Unit\Countries;

use App\Models\Countries\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class CountryTest extends TestCase
{

    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_can_create_country(): void
    {
        $payload = [
            'name' => 'Brazil',
            'code' => 'BR',
        ];

        $response = $this->postJson('/api/countries', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('countries', [
            'name' => 'Brazil',
            'code' => 'BR',
        ]);
    }

    public function test_country_index_returns_paginated_data(): void
    {
        Country::factory()->create([
            'name' => 'Iraq',
            'code' => 'IQ',
        ]);

        Country::factory()->create([
            'name' => 'Turkey',
            'code' => 'TR',
        ]);

        $response = $this->getJson('/api/countries');

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

    public function test_create_country_requires_name_and_code(): void
    {
        $response = $this->postJson('/api/countries', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'code',
            ]);
    }

    public function test_cannot_create_country_with_duplicate_code(): void
    {
        Country::factory()->create([
            'name' => 'Iraq',
            'code' => 'IQ',
        ]);

        $payload = [
            'name' => 'Iraq Duplicate',
            'code' => 'IQ', // same code
        ];

        $response = $this->postJson('/api/countries', $payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        $this->assertEquals(
            1,
            Country::where('code', 'IQ')->count(),
            'There should only be one country with code IQ'
        );
    }

    public function test_unauthenticated_user_cannot_create_country(): void
    {
        auth()->logout();

        $payload = [
            'name' => 'Germany',
            'code' => 'DE',
        ];
        $response = $this->postJson('/api/countries', $payload);

        $response->assertStatus(401);
    }
}
