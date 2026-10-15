<?php

namespace Tests\Feature;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bikin data contoh yang valid buat endpoint simpan/update.
     *
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'phone' => '081234567890',
            'level' => 2,
            'vehicle_type' => VehicleType::Motorcycle->value,
            'vehicle_plate_number' => 'B 1234 XYZ',
            'city' => 'Bandung',
            'address' => 'Jl. Merdeka No. 1',
            'status' => CourierStatus::Active->value,
            'registered_at' => '2024-01-15',
            'notes' => 'Experienced courier.',
        ], $overrides);
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function test_index_returns_a_paginated_list_of_couriers(): void
    {
        Courier::factory()->count(20)->create();

        $response = $this->getJson('/api/couriers');

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'level',
                        'vehicle_type',
                        'vehicle_type_label',
                        'vehicle_plate_number',
                        'city',
                        'address',
                        'status',
                        'status_label',
                        'registered_at',
                        'notes',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_index_is_sorted_by_name_by_default(): void
    {
        Courier::factory()->create(['name' => 'Zulfikar']);
        Courier::factory()->create(['name' => 'Andi']);
        Courier::factory()->create(['name' => 'Marlina']);

        $response = $this->getJson('/api/couriers');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name');

        $this->assertSame(['Andi', 'Marlina', 'Zulfikar'], $names->all());
    }

    public function test_index_can_be_sorted_by_registration_date(): void
    {
        Courier::factory()->create(['name' => 'Older', 'registered_at' => '2020-01-01']);
        Courier::factory()->create(['name' => 'Newer', 'registered_at' => '2024-01-01']);

        $response = $this->getJson('/api/couriers?sort=registered_at');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name');

        // Ascending sebagai default -> yang paling lama didaftar duluan.
        $this->assertSame(['Older', 'Newer'], $names->all());
    }

    public function test_index_sort_direction_can_be_overridden(): void
    {
        Courier::factory()->create(['name' => 'Older', 'registered_at' => '2020-01-01']);
        Courier::factory()->create(['name' => 'Newer', 'registered_at' => '2024-01-01']);

        $response = $this->getJson('/api/couriers?sort=registered_at&direction=desc');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name');

        $this->assertSame(['Newer', 'Older'], $names->all());
    }

    public function test_index_falls_back_to_name_sort_for_unknown_sort_column(): void
    {
        Courier::factory()->create(['name' => 'Zulfikar']);
        Courier::factory()->create(['name' => 'Andi']);

        $response = $this->getJson('/api/couriers?sort=malicious_column');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name');

        $this->assertSame(['Andi', 'Zulfikar'], $names->all());
    }

    public function test_index_search_matches_multiple_name_tokens(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Siti Rahayu']);

        // "budi agung" harusnya cocok ke "Budiono Hadi Agung" walaupun dua
        // katanya nggak nyambung langsung di nama.
        $response = $this->getJson('/api/couriers?search=budi+agung');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name');

        $this->assertContains('Budiono Hadi Agung', $names->all());
        $this->assertNotContains('Siti Rahayu', $names->all());
    }

    public function test_index_search_requires_all_tokens_to_match(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Agus Setiawan']);

        // Dua-duanya harus ada -> cuma "Budiono Hadi Agung" yang lolos.
        $response = $this->getJson('/api/couriers?search=budi+agung');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');
    }

    public function test_index_search_is_case_insensitive(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);

        $response = $this->getJson('/api/couriers?search=BUDI');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_filters_by_multiple_levels(): void
    {
        Courier::factory()->count(3)->level(1)->create();
        Courier::factory()->count(2)->level(2)->create();
        Courier::factory()->count(4)->level(3)->create();
        Courier::factory()->count(5)->level(4)->create();

        $response = $this->getJson('/api/couriers?level=2,3');

        $response->assertOk()
            ->assertJsonCount(6, 'data');

        $levels = collect($response->json('data'))->pluck('level')->unique()->sort()->values();

        $this->assertSame([2, 3], $levels->all());
    }

    public function test_index_ignores_out_of_range_levels(): void
    {
        Courier::factory()->count(2)->level(2)->create();

        // Level 9 dan 99 di luar rentang yang didukung, jadi diabaikan.
        $response = $this->getJson('/api/couriers?level=2,9,99');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_can_combine_search_and_level_filters(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung', 'level' => 3]);
        Courier::factory()->create(['name' => 'Budi Agung Pratama', 'level' => 1]);

        $response = $this->getJson('/api/couriers?search=budi+agung&level=3');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function test_show_returns_the_full_courier_data(): void
    {
        $courier = Courier::factory()->create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi.lestari@example.com',
            'level' => 4,
        ]);

        $response = $this->getJson("/api/couriers/{$courier->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $courier->id)
            ->assertJsonPath('data.name', 'Dewi Lestari')
            ->assertJsonPath('data.email', 'dewi.lestari@example.com')
            ->assertJsonPath('data.level', 4)
            ->assertJsonPath('data.vehicle_type', $courier->vehicle_type->value)
            ->assertJsonPath('data.status', $courier->status->value);
    }

    public function test_show_returns_404_for_missing_courier(): void
    {
        $this->getJson('/api/couriers/999999')->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function test_store_creates_a_courier_and_persists_it(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/couriers', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Budi Santoso')
            ->assertJsonPath('data.email', 'budi.santoso@example.com');

        $this->assertDatabaseHas('couriers', [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'level' => 2,
            'vehicle_type' => VehicleType::Motorcycle->value,
        ]);
    }

    public function test_store_requires_the_expected_fields(): void
    {
        $this->postJson('/api/couriers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'level', 'vehicle_type', 'registered_at']);
    }

    public function test_store_rejects_level_outside_1_to_5(): void
    {
        $this->postJson('/api/couriers', $this->validPayload(['level' => 0]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['level']);

        $this->postJson('/api/couriers', $this->validPayload(['level' => 6]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['level']);
    }

    public function test_store_accepts_boundary_levels_1_and_5(): void
    {
        $this->postJson('/api/couriers', $this->validPayload([
            'email' => 'level1@example.com',
            'level' => 1,
        ]))->assertCreated();

        $this->postJson('/api/couriers', $this->validPayload([
            'email' => 'level5@example.com',
            'level' => 5,
            'vehicle_plate_number' => 'B 5678 ABC',
        ]))->assertCreated();

        $this->assertDatabaseHas('couriers', ['email' => 'level1@example.com', 'level' => 1]);
        $this->assertDatabaseHas('couriers', ['email' => 'level5@example.com', 'level' => 5]);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        Courier::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/couriers', $this->validPayload(['email' => 'taken@example.com']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_rejects_invalid_email(): void
    {
        $this->postJson('/api/couriers', $this->validPayload(['email' => 'not-an-email']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_rejects_invalid_vehicle_type(): void
    {
        $this->postJson('/api/couriers', $this->validPayload(['vehicle_type' => 'helicopter']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['vehicle_type']);
    }

    public function test_store_rejects_invalid_registered_at(): void
    {
        $this->postJson('/api/couriers', $this->validPayload(['registered_at' => 'not-a-date']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['registered_at']);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function test_update_modifies_the_courier_and_persists_it(): void
    {
        $courier = Courier::factory()->create([
            'name' => 'Old Name',
            'level' => 1,
        ]);

        $payload = $this->validPayload([
            'name' => 'New Name',
            'level' => 5,
            'vehicle_type' => VehicleType::Van->value,
        ]);

        $response = $this->putJson("/api/couriers/{$courier->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.level', 5);

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'name' => 'New Name',
            'level' => 5,
            'vehicle_type' => VehicleType::Van->value,
        ]);
    }

    public function test_update_ignores_current_record_when_checking_unique_email(): void
    {
        $courier = Courier::factory()->create(['email' => 'same@example.com']);

        // Ngupdate data tapi emailnya tetap sama harusnya tetap boleh.
        $this->putJson("/api/couriers/{$courier->id}", $this->validPayload(['email' => 'same@example.com']))
            ->assertOk();

        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'email' => 'same@example.com']);
    }

    public function test_update_rejects_email_used_by_another_courier(): void
    {
        $courier = Courier::factory()->create(['email' => 'mine@example.com']);
        $other = Courier::factory()->create(['email' => 'other@example.com']);

        $this->putJson("/api/couriers/{$courier->id}", $this->validPayload(['email' => $other->email]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_validates_input(): void
    {
        $courier = Courier::factory()->create();

        $this->putJson("/api/couriers/{$courier->id}", $this->validPayload(['level' => 42]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['level']);

        $this->assertDatabaseMissing('couriers', ['id' => $courier->id, 'level' => 42]);
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function test_destroy_deletes_the_courier(): void
    {
        $courier = Courier::factory()->create();

        $response = $this->deleteJson("/api/couriers/{$courier->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Courier deleted successfully.');

        $this->assertDatabaseMissing('couriers', ['id' => $courier->id]);
    }

    public function test_destroy_returns_404_for_missing_courier(): void
    {
        $this->deleteJson('/api/couriers/999999')->assertNotFound();
    }
}
