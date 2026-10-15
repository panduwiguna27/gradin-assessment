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
     * The lowest and highest courier level allowed.
     */
    public const LEVEL_MIN = 1;

    public const LEVEL_MAX = 5;

    /**
     * The attributes that are mass assignable.
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
     * The attributes that should be cast to native types.
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
     * Scope a query to only couriers whose level is within the given list.
     *
     * @param  array<int, int>  $levels
     */
    public function scopeOfLevels(Builder $query, array $levels): Builder
    {
        return $query->whereIn('level', $levels);
    }
}
