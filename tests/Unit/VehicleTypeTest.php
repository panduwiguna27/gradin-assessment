<?php

namespace Tests\Unit;

use App\Enums\VehicleType;
use PHPUnit\Framework\TestCase;

class VehicleTypeTest extends TestCase
{
    /**
     * The values() helper should return every case value in definition order.
     */
    public function test_values_returns_all_case_values(): void
    {
        $this->assertSame(
            ['motorcycle', 'car', 'van', 'truck'],
            VehicleType::values(),
        );
    }

    /**
     * Every case must expose a human readable label.
     */
    public function test_labels_are_correct_for_each_case(): void
    {
        $this->assertSame('Motorcycle', VehicleType::Motorcycle->label());
        $this->assertSame('Car', VehicleType::Car->label());
        $this->assertSame('Van', VehicleType::Van->label());
        $this->assertSame('Truck', VehicleType::Truck->label());
    }

    /**
     * The enum should only contain the four expected cases.
     */
    public function test_it_declares_the_expected_number_of_cases(): void
    {
        $this->assertCount(4, VehicleType::cases());
    }
}
