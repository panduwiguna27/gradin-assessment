<?php

namespace Database\Factories;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    /**
     * Model yang jadi tuan rumah factory ini.
     *
     * @var class-string<Courier>
     */
    protected $model = Courier::class;

    /**
     * Data default yang dibikin tiap kali factory dipanggil.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake('id_ID')->name();

        return [
            'name' => $name,
            'email' => fake('id_ID')->unique()->safeEmail(),
            'phone' => fake('id_ID')->numerify('08##########'),
            'level' => fake()->numberBetween(Courier::LEVEL_MIN, Courier::LEVEL_MAX),
            'vehicle_type' => fake()->randomElement(VehicleType::values()),
            'vehicle_plate_number' => fake('id_ID')->bothify('? #### ???'),
            'city' => fake('id_ID')->city(),
            'address' => fake('id_ID')->address(),
            'status' => fake()->randomElement(CourierStatus::values()),
            'registered_at' => fake()->dateTimeBetween('-3 years', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Paksa kurir yang dibikin punya level tertentu.
     */
    public function level(int $level): static
    {
        return $this->state(fn (array $attributes) => ['level' => $level]);
    }
}
