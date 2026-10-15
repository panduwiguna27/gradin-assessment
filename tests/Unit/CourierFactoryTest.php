<?php

namespace Tests\Unit;

use App\Http\Requests\Api\StoreCourierRequest;
use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CourierFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Data yang dihasilin factory harusnya lolos aturan validasi yang sama
     * persis kayak pas endpoint simpan data (store).
     */
    public function test_factory_generates_records_that_pass_store_validation(): void
    {
        $rules = StoreCourierRequest::create('/api/couriers', 'POST')->rules();

        // Bikin beberapa data karena level, jenis kendaraan & status dipilih
        // acak, jadi satu data doang nggak bakal ngelewatin semua variasi nilai.
        for ($i = 0; $i < 10; $i++) {
            $attributes = Courier::factory()->raw();

            // Factory ngasilin instance DateTime buat registered_at; user asli
            // biasanya ngirim string tanggal, jadi dirapiin dulu sebelum divalidasi.
            $attributes['registered_at'] = $attributes['registered_at']->format('Y-m-d');

            $validator = Validator::make($attributes, $rules);

            $this->assertFalse(
                $validator->fails(),
                'Factory output failed validation: '.$validator->errors()->toJson(),
            );
        }
    }

    /**
     * Level yang dihasilin factory harusnya selalu di rentang 1-5.
     */
    public function test_factory_level_is_within_the_supported_range(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $level = Courier::factory()->raw()['level'];

            $this->assertGreaterThanOrEqual(Courier::LEVEL_MIN, $level);
            $this->assertLessThanOrEqual(Courier::LEVEL_MAX, $level);
        }
    }

    /**
     * State level() di factory harusnya bisa maksain level kurir sesuai permintaan.
     */
    public function test_level_state_overrides_the_generated_level(): void
    {
        $courier = Courier::factory()->level(5)->create();

        $this->assertSame(5, $courier->level);
    }

    /**
     * Factory harusnya bisa nyimpen model lengkap yang bisa diambil lagi.
     */
    public function test_factory_persists_a_valid_model(): void
    {
        $courier = Courier::factory()->create();

        $this->assertDatabaseHas('couriers', ['id' => $courier->id]);
        $this->assertNotNull(Courier::find($courier->id));
    }
}
