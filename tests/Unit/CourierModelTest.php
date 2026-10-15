<?php

namespace Tests\Unit;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CourierModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The level boundary constants must match the 1-5 requirement.
     */
    public function test_level_boundary_constants_are_correct(): void
    {
        $this->assertSame(1, Courier::LEVEL_MIN);
        $this->assertSame(5, Courier::LEVEL_MAX);
    }

    /**
     * The attributes defined in $fillable must be exactly the master-data
     * columns that the API is allowed to mass-assign.
     */
    public function test_fillable_contains_the_expected_columns(): void
    {
        $expected = [
            'name',
            'email',
            'phone',
            'level',
            'vehicle_type',
            'vehicle_plate_number',
            'city',
            'address',
            'status',
            'registered_at',
            'notes',
        ];

        $this->assertSame($expected, (new Courier)->getFillable());
    }

    /**
     * Attributes must be cast to their native types automatically.
     */
    public function test_attributes_are_cast_to_native_types(): void
    {
        $courier = Courier::factory()->create([
            'level' => 3,
            'vehicle_type' => VehicleType::Van,
            'status' => CourierStatus::Active,
            'registered_at' => '2024-01-15',
        ]);

        $this->assertIsInt($courier->level);
        $this->assertInstanceOf(VehicleType::class, $courier->vehicle_type);
        $this->assertInstanceOf(CourierStatus::class, $courier->status);
        $this->assertInstanceOf(Carbon::class, $courier->registered_at);
    }

    /**
     * A freshly retrieved model must re-cast enum attributes correctly.
     */
    public function test_enum_casts_survive_a_round_trip_from_the_database(): void
    {
        $courier = Courier::factory()->create([
            'vehicle_type' => VehicleType::Truck,
            'status' => CourierStatus::Suspended,
        ]);

        $fresh = Courier::find($courier->id);

        $this->assertSame(VehicleType::Truck, $fresh->vehicle_type);
        $this->assertSame(CourierStatus::Suspended, $fresh->status);
    }

    /**
     * The ofLevels scope must restrict the query to the given levels only.
     */
    public function test_of_levels_scope_returns_only_matching_levels(): void
    {
        Courier::factory()->count(3)->level(1)->create();
        Courier::factory()->count(2)->level(2)->create();
        Courier::factory()->count(4)->level(3)->create();
        Courier::factory()->count(5)->level(4)->create();

        $result = Courier::ofLevels([2, 3])->get();

        $this->assertCount(6, $result);
        $this->assertSame(
            [2, 3],
            $result->pluck('level')->unique()->sort()->values()->all(),
        );
    }
}
