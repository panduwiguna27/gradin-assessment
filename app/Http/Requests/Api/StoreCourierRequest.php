<?php

namespace App\Http\Requests\Api;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('couriers', 'email')],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+\\-\\s()]+$/'],
            'level' => ['required', 'integer', 'min:'.Courier::LEVEL_MIN, 'max:'.Courier::LEVEL_MAX],
            'vehicle_type' => ['required', Rule::enum(VehicleType::class)],
            'vehicle_plate_number' => ['nullable', 'string', 'max:32', Rule::unique('couriers', 'vehicle_plate_number')],
            'city' => ['nullable', 'string', 'max:128'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::enum(CourierStatus::class)],
            'registered_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kurir wajib diisi.',
            'email.required' => 'Email kurir wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'level.required' => 'Level kurir wajib diisi.',
            'level.min' => 'Level minimal adalah '.Courier::LEVEL_MIN.'.',
            'level.max' => 'Level maksimal adalah '.Courier::LEVEL_MAX.'.',
            'vehicle_type.required' => 'Jenis kendaraan wajib diisi.',
            'vehicle_type.Illuminate\Validation\Rules\Enum' => 'Jenis kendaraan tidak valid.',
            'registered_at.required' => 'Tanggal didaftarkan wajib diisi.',
            'registered_at.date' => 'Format tanggal didaftarkan tidak valid.',
        ];
    }

    /**
     * Custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama kurir',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'level' => 'level',
            'vehicle_type' => 'jenis kendaraan',
            'vehicle_plate_number' => 'nomor plat kendaraan',
            'city' => 'kota',
            'address' => 'alamat',
            'status' => 'status',
            'registered_at' => 'tanggal didaftarkan',
            'notes' => 'catatan',
        ];
    }
}
