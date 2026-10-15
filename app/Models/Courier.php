<?php

namespace App\Models;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use Database\Factories\CourierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    /** @use HasFactory<CourierFactory> */
    use HasFactory;

    /**
     * Batas level kurir yang boleh dipake, dari paling rendah sampai paling tinggi.
     */
    public const LEVEL_MIN = 1;

    public const LEVEL_MAX = 5;

    /**
     * Kolom yang boleh diisi bareng-bareng (mass assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
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

    /**
     * Kolom yang perlu diubah/dibaca otomatis ke tipe aslinya (casting).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'vehicle_type' => VehicleType::class,
            'status' => CourierStatus::class,
            'registered_at' => 'date',
        ];
    }

    /**
     * Scope buat nyaring kurir yang level-nya ada di daftar yang diminta.
     *
     * @param  array<int, int>  $levels
     */
    public function scopeOfLevels(Builder $query, array $levels): Builder
    {
        return $query->whereIn('level', $levels);
    }
}
