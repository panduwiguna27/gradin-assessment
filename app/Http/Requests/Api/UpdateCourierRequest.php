<?php

namespace App\Http\Requests\Api;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends StoreCourierRequest
{
    /**
     * Cek apakah user boleh ngirim request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi yang dipake buat request ini.
     *
     * Aturannya mirip kayak waktu simpan data baru, bedanya aturan "unik"
     * (email & plat) nggak ngitung kurir yang lagi diupdate ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courier = $this->route('courier');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('couriers', 'email')->ignore($courier)],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+\-\s()]+$/'],
            'level' => ['required', 'integer', 'min:'.Courier::LEVEL_MIN, 'max:'.Courier::LEVEL_MAX],
            'vehicle_type' => ['required', Rule::enum(VehicleType::class)],
            'vehicle_plate_number' => ['nullable', 'string', 'max:32', Rule::unique('couriers', 'vehicle_plate_number')->ignore($courier)],
            'city' => ['nullable', 'string', 'max:128'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::enum(CourierStatus::class)],
            'registered_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
