<?php

namespace App\Enums;

/**
 * Status aktif/tidaknya kurir.
 */
enum CourierStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    /**
     * Label biar enak dibaca manusia buat tiap status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
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
