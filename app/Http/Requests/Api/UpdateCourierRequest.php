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
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The rules mirror the store request, except the unique constraints
     * ignore the record currently being updated.
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
