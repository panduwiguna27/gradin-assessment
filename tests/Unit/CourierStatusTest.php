<?php

namespace Tests\Unit;

use App\Enums\CourierStatus;
use PHPUnit\Framework\TestCase;

class CourierStatusTest extends TestCase
{
    /**
     * The values() helper should return every case value in definition order.
     */
    public function test_values_returns_all_case_values(): void
    {
        $this->assertSame(
            ['active', 'inactive', 'suspended'],
            CourierStatus::values(),
        );
    }

    /**
     * Every case must expose a human readable label.
     */
    public function test_labels_are_correct_for_each_case(): void
    {
        $this->assertSame('Active', CourierStatus::Active->label());
        $this->assertSame('Inactive', CourierStatus::Inactive->label());
        $this->assertSame('Suspended', CourierStatus::Suspended->label());
    }

    /**
     * The enum should only contain the three expected cases.
     */
    public function test_it_declares_the_expected_number_of_cases(): void
    {
        $this->assertCount(3, CourierStatus::cases());
    }
}
