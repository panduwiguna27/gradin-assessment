<?php

namespace App\Http\Resources;

use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Courier
 */
class CourierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'level' => $this->level,
            'vehicle_type' => $this->vehicle_type?->value,
            'vehicle_type_label' => $this->vehicle_type?->label(),
            'vehicle_plate_number' => $this->vehicle_plate_number,
            'city' => $this->city,
            'address' => $this->address,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'registered_at' => $this->registered_at?->toDateString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
