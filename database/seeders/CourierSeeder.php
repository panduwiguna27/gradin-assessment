<?php

namespace Database\Seeders;

use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates a small deterministic dataset that is useful for manually
     * exercising the search, level filter and sorting behaviour.
     */
    public function run(): void
    {
        // A record specifically designed to exercise the "budi agung" search.
        Courier::factory()->create([
            'name' => 'Budiono Hadi Agung',
            'email' => 'budiono.agung@example.com',
            'level' => 3,
            'vehicle_type' => 'motorcycle',
            'city' => 'Bandung',
            'registered_at' => '2024-01-15',
        ]);

        Courier::factory()
            ->count(14)
            ->create();
    }
}
