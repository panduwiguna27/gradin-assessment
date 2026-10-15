<?php

namespace App\Enums;

/**
 * Jenis kendaraan yang bisa dipake kurir.
 */
enum VehicleType: string
{
    case Motorcycle = 'motorcycle';
    case Car = 'car';
    case Van = 'van';
    case Truck = 'truck';

    /**
     * Label biar enak dibaca manusia buat tiap jenis kendaraan.
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
     * Semua nilai dalam bentuk array biasa (buat dipake di aturan validasi).
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
