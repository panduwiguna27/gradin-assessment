<?php

namespace Tests\Unit;

use App\Enums\CourierStatus;
use PHPUnit\Framework\TestCase;

class CourierStatusTest extends TestCase
{
    /**
     * Helper values() harusnya balikin semua nilai case sesuai urutan bikinnya.
     */
    public function test_values_returns_all_case_values(): void
    {
        $this->assertSame(
            ['active', 'inactive', 'suspended'],
            CourierStatus::values(),
        );
    }

    /**
     * Tiap case harus punya label yang enak dibaca.
     */
    public function test_labels_are_correct_for_each_case(): void
    {
        $this->assertSame('Active', CourierStatus::Active->label());
        $this->assertSame('Inactive', CourierStatus::Inactive->label());
        $this->assertSame('Suspended', CourierStatus::Suspended->label());
    }

    /**
     * Enum-nya harusnya cuma punya tiga case.
     */
    public function test_it_declares_the_expected_number_of_cases(): void
    {
        $this->assertCount(3, CourierStatus::cases());
    }
}
