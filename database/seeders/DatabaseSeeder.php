<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Isi database aplikasi pakai data contoh.
     */
    public function run(): void
    {
        $this->call([
            CourierSeeder::class,
        ]);
    }
}
