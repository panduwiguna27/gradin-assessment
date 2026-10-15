<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 32)->nullable();

            // Operational details
            $table->unsignedTinyInteger('level')->default(1); // Kurir level: 1-5
            $table->string('vehicle_type', 32)->nullable();   // motorcycle, car, van, truck
            $table->string('vehicle_plate_number', 32)->nullable();
            $table->string('city', 128)->nullable();
            $table->text('address')->nullable();

            // Lifecycle
            $table->string('status', 32)->default('active');  // active, inactive, suspended
            $table->date('registered_at')->nullable();        // tanggal didaftarkan

            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes for common query / sort paths
            $table->index('name');
            $table->index('level');
            $table->index('status');
            $table->index('registered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
