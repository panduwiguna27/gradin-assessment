<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCourierRequest;
use App\Http\Requests\Api\UpdateCourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourierController extends Controller
{
    /**
     * Daftar kolom yang boleh dipake buat ngurutin. Ini semacam "whitelist"
     * biar parameter "sort" dari URL nggak bisa nyelipin nama kolom sembarang
     * ke database (biar aman dari SQL injection lewat urutan).
     *
     * @var array<string, string>
     */
    private const SORTABLE_COLUMNS = [
        'name' => 'name',
        'registered_at' => 'registered_at',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'level' => 'level',
    ];

    /**
     * Tampilin daftar kurir (dengan pagination).
     *
     * Parameter query yang didukung:
     *  - search       : kata kunci dipisah spasi, tiap kata harus cocok sama
     *                   kurir (contoh "budi agung" cocok ke "Budiono Hadi Agung").
     *  - level        : daftar level dipisah koma (contoh "2,3").
     *  - sort         : kolom buat ngurutin. Default-nya "name".
     *  - direction    : "asc" | "desc". Default-nya "asc".
     *  - per_page     : jumlah data per halaman. Default-nya 15.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $couriers = Courier::query()
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, (string) $request->query('search')))
            ->when($request->filled('level'), fn (Builder $query) => $this->applyLevelFilter($query, (string) $request->query('level')))
            ->orderBy(...$this->resolveSorting($request))
            ->orderBy('id')
            ->paginate($this->resolvePerPage($request))
            ->appends($request->query());

        return CourierResource::collection($couriers);
    }

    /**
     * Simpan kurir baru ke database.
     */
    public function store(StoreCourierRequest $request): JsonResponse
    {
        $courier = Courier::create($request->validated());

        return (new CourierResource($courier))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Tampilin detail satu kurir.
     */
    public function show(Courier $courier): CourierResource
    {
        return new CourierResource($courier);
    }

    /**
     * Update data kurir yang udah ada di database.
     */
    public function update(UpdateCourierRequest $request, Courier $courier): CourierResource
    {
        $courier->update($request->validated());

        return new CourierResource($courier->fresh());
    }

    /**
     * Hapus kurir dari database.
     */
    public function destroy(Courier $courier): JsonResponse
    {
        $courier->delete();

        return response()->json([
            'message' => 'Courier deleted successfully.',
        ]);
    }

    /**
     * Terapin pencarian berdasarkan kata (token), nggak peduli huruf besar/kecil.
     *
     * Tiap kata (dipisah spasi) harus ketemu di salah satu kolom yang dicari.
     * Antarkata digabung pakai AND, antarkolom pakai OR, makanya "budi agung"
     * tetap bisa cocok ke "Budiono Hadi Agung".
     */
    private function applySearch(Builder $query, string $search): void
    {
        $tokens = preg_split('/\s+/', trim($search)) ?: [];
        $tokens = array_filter($tokens, fn (string $token): bool => $token !== '');

        if ($tokens === []) {
            return;
        }

        $columns = ['name', 'email', 'phone', 'vehicle_plate_number'];

        $query->where(function (Builder $query) use ($tokens, $columns): void {
            foreach ($tokens as $token) {
                $like = '%'.str_replace('%', '\%', $token).'%';

                $query->where(function (Builder $query) use ($columns, $like): void {
                    foreach ($columns as $column) {
                        $query->orWhere($column, 'like', $like);
                    }
                });
            }
        });
    }

    /**
     * Terapin filter level dari "?level=2,3".
     *
     * Nerima daftar level dipisah koma, terus cuma ambil yang angkanya masuk
     * rentang level yang didukung (1-5).
     */
    private function applyLevelFilter(Builder $query, string $levels): void
    {
        $requested = array_filter(
            array_map('intval', explode(',', $levels)),
            fn (int $level): bool => $level >= Courier::LEVEL_MIN && $level <= Courier::LEVEL_MAX,
        );

        if ($requested === []) {
            return;
        }

        $query->whereIn('level', array_values(array_unique($requested)));
    }

    /**
     * Tentukan kolom & arah urutan dari request.
     *
     * Kalau kolomnya nggak diisi atau nggak ada di whitelist, otomatis balik
     * lagi ngurutin pakai nama (urutan naik / ascending).
     *
     * @return array{0: string, 1: string}
     */
    private function resolveSorting(Request $request): array
    {
        $requested = (string) $request->query('sort', 'name');
        $column = self::SORTABLE_COLUMNS[$requested] ?? self::SORTABLE_COLUMNS['name'];

        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        return [$column, $direction];
    }

    /**
     * Tentukan jumlah data per halaman, dibatesin biar nggak kelamaan.
     */
    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 15);

        if ($perPage <= 0) {
            return 15;
        }

        return min($perPage, 100);
    }
}
