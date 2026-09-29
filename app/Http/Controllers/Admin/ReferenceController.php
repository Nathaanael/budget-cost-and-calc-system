<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.reference.reference', [
            'title' => __('Reference'),
            ...$this->getReferences($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $references = $this->getReferences($request)['references'];

        return response()->json([
            'data' => $references->items(),
            'meta' => [
                'current_page' => $references->currentPage(),
                'last_page' => $references->lastPage(),
                'from' => $references->firstItem() ?? 0,
                'to' => $references->lastItem() ?? 0,
                'total' => $references->total(),
                'per_page' => $references->perPage(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.user.maintenance.reference.createReference', [
            'title' => __('Tambah Reference'),
        ]);
    }

    private function getReferences(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sortableFields = ['code', 'description_1', 'description_2', 'period', 'period_description', 'rate_current'];
        $sort = in_array($request->string('sort')->toString(), $sortableFields, true)
            ? $request->string('sort')->toString()
            : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $page = max(1, $request->integer('page', 1));
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;

        $allReferences = collect([
            ['code' => 'REF-001', 'description_1' => 'Reference Budget 2026', 'description_2' => 'Periode Januari', 'period' => '2026-01'],
            ['code' => 'REF-002', 'description_1' => 'Reference Budget 2026', 'description_2' => 'Periode Februari', 'period' => '2026-02'],
            ['code' => 'REF-003', 'description_1' => 'Reference Budget 2026', 'description_2' => 'Periode Maret', 'period' => '2026-03'],
            ['code' => 'REF-004', 'description_1' => 'Reference Forecast 2026', 'description_2' => 'Periode April', 'period' => '2026-04'],
            ['code' => 'REF-005', 'description_1' => 'Reference Forecast 2026', 'description_2' => 'Periode Mei', 'period' => '2026-05'],
            ['code' => 'REF-006', 'description_1' => 'Reference Forecast 2026', 'description_2' => 'Periode Juni', 'period' => '2026-06'],
            ['code' => 'REF-007', 'description_1' => 'Reference LE 2026', 'description_2' => 'Periode Juli', 'period' => '2026-07'],
            ['code' => 'REF-008', 'description_1' => 'Reference LE 2026', 'description_2' => 'Periode Agustus', 'period' => '2026-08'],
            ['code' => 'REF-009', 'description_1' => 'Reference LE 2026', 'description_2' => 'Periode September', 'period' => '2026-09'],
            ['code' => 'REF-010', 'description_1' => 'Reference Closing 2026', 'description_2' => 'Periode Oktober', 'period' => '2026-10'],
        ])->map(function (array $item, int $index) {
            $sequence = $index + 1;
            $rate = 15000 + ($sequence * 125);

            return [
                ...$item,
                'period_description' => 'Periode '.$item['period'],
                'rate_current' => $rate,
                'rate_le' => $rate + 25,
                'rate_1' => $rate + 50,
                'rate_2' => $rate + 75,
                'rate_3' => $rate + 100,
                'rate_4' => $rate + 125,
                ...$this->peValues('ckp', 100 + $sequence),
                ...$this->peValues('smg', 200 + $sequence),
                ...$this->peValues('sby', 300 + $sequence),
            ];
        });

        $filteredReferences = $allReferences
            ->when($search !== '', function ($items) use ($search) {
                return $items->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['code']), strtolower($search))
                        || str_contains(strtolower($item['description_1']), strtolower($search))
                        || str_contains(strtolower($item['description_2']), strtolower($search));
                });
            })
            ->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')
            ->values();

        $references = new LengthAwarePaginator(
            $filteredReferences->forPage($page, $perPage)->values(),
            $filteredReferences->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->except('page')],
        );

        return compact('references', 'search', 'sort', 'direction', 'perPage');
    }

    private function peValues(string $location, int $base): array
    {
        return [
            "pe_{$location}_le" => $base,
            "pe_{$location}_1" => $base + 1,
            "pe_{$location}_2" => $base + 2,
            "pe_{$location}_3" => $base + 3,
            "pe_{$location}_4" => $base + 4,
        ];
    }
}
