<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalanin migration (bikin tabel).
     */
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();

            // Identitas kurir
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 32)->nullable();

            // Data operasional
            $table->unsignedTinyInteger('level')->default(1); // level kurir: 1-5
            $table->string('vehicle_type', 32)->nullable();   // motorcycle, car, van, truck
            $table->string('vehicle_plate_number', 32)->nullable();
            $table->string('city', 128)->nullable();
            $table->text('address')->nullable();

            // Status keaktifan
            $table->string('status', 32)->default('active');  // active, inactive, suspended
            $table->date('registered_at')->nullable();        // tanggal didaftarkan

            $table->text('notes')->nullable();

            $table->timestamps();

            // Index biar query & sorting yang sering dipake jadi lebih cepat
            $table->index('name');
            $table->index('level');
            $table->index('status');
            $table->index('registered_at');
        });
    }

    /**
     * Balikin migration (hapus tabelnya).
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
