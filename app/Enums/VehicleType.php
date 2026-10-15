<?php

namespace App\Enums;

/**
 * Supported courier vehicle types used across the application.
 */
enum VehicleType: string
{
    case Motorcycle = 'motorcycle';
    case Car = 'car';
    case Van = 'van';
    case Truck = 'truck';

    /**
     * Human readable label for the vehicle type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Motorcycle => 'Motorcycle',
            self::Car => 'Car',
            self::Van => 'Van',
            self::Truck => 'Truck',
        };
    }

    /**
     * All values as a plain array (handy for validation rules).
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
