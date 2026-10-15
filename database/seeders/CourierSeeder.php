<?php

namespace Database\Seeders;

use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    /**
     * Isi database pakai data contoh.
     *
     * Bikin sedikit data tetap yang enak buat nyoba sendiri fitur cari,
     * filter level, dan urutan (sorting).
     */
    public function run(): void
    {
        // Satu baris yang sengaja dibikin buat ngetes pencarian "budi agung".
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
