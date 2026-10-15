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
     * Columns the API is allowed to sort by. Used to whitelist the "sort"
     * query parameter so arbitrary column names can never reach the database.
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
     * Display a listing of the couriers.
     *
     * Supported query parameters:
     *  - search       : space separated tokens, every token must match the
     *                   courier (e.g. "budi agung" matches "Budiono Hadi Agung").
     *  - level        : comma separated list of levels (e.g. "2,3").
     *  - sort         : column to sort by. Defaults to "name".
     *  - direction    : "asc" | "desc". Defaults to "asc".
     *  - per_page     : number of items per page. Defaults to 15.
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
     * Store a newly created courier in storage.
     */
    public function store(StoreCourierRequest $request): JsonResponse
    {
        $courier = Courier::create($request->validated());

        return (new CourierResource($courier))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified courier.
     */
    public function show(Courier $courier): CourierResource
    {
        return new CourierResource($courier);
    }

    /**
     * Update the specified courier in storage.
     */
    public function update(UpdateCourierRequest $request, Courier $courier): CourierResource
    {
        $courier->update($request->validated());

        return new CourierResource($courier->fresh());
    }

    /**
     * Remove the specified courier from storage.
     */
    public function destroy(Courier $courier): JsonResponse
    {
        $courier->delete();

        return response()->json([
            'message' => 'Courier deleted successfully.',
        ]);
    }

    /**
     * Apply a tokenised, case-insensitive search to the query.
     *
     * Every whitespace separated token must be found in at least one of the
     * searchable columns. Tokens are combined with AND, while the columns are
     * combined with OR, so "budi agung" still matches "Budiono Hadi Agung".
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
     * Apply the "?level=2,3" filter to the query.
     *
     * Accepts a comma separated list of levels and keeps only the values that
     * fall within the supported level range (1-5).
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
     * Resolve the sort column and direction from the request.
     *
     * Falls back to sorting by name (ascending) when the requested column is
     * missing or not whitelisted.
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
     * Resolve the number of items per page, clamped to a sane range.
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
