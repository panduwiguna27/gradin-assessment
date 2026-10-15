<?php

namespace Tests\Unit;

use App\Enums\VehicleType;
use PHPUnit\Framework\TestCase;

class VehicleTypeTest extends TestCase
{
    /**
     * Helper values() harusnya balikin semua nilai case sesuai urutan bikinnya.
     */
    public function test_values_returns_all_case_values(): void
    {
        $this->assertSame(
            ['motorcycle', 'car', 'van', 'truck'],
            VehicleType::values(),
        );
    }

    /**
     * Tiap case harus punya label yang enak dibaca.
     */
    public function test_labels_are_correct_for_each_case(): void
    {
        $this->assertSame('Motorcycle', VehicleType::Motorcycle->label());
        $this->assertSame('Car', VehicleType::Car->label());
        $this->assertSame('Van', VehicleType::Van->label());
        $this->assertSame('Truck', VehicleType::Truck->label());
    }

    /**
     * Enum-nya harusnya cuma punya empat case.
     */
    public function test_it_declares_the_expected_number_of_cases(): void
    {
        $this->assertCount(4, VehicleType::cases());
    }
}
