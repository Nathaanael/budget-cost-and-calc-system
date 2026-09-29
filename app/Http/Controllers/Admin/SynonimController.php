<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\FinishedGoodCatalog;
use App\Support\RawMaterialCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SynonimController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.synonim.synonim', [
            'title' => __('Synonim'),
            ...$this->getSynonims($request),
            ...$this->getOptions(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $synonims = $this->getSynonims($request)['synonims'];

        return response()->json([
            'data' => $synonims->items(),
            'meta' => [
                'current_page' => $synonims->currentPage(),
                'last_page' => $synonims->lastPage(),
                'from' => $synonims->firstItem() ?? 0,
                'to' => $synonims->lastItem() ?? 0,
                'total' => $synonims->total(),
                'per_page' => $synonims->perPage(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.user.maintenance.synonim.createSynonim', [
            'title' => __('Tambah Synonim'),
            ...$this->getOptions(),
        ]);
    }

    private function getSynonims(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sortableFields = ['rm_code', 'rm_description', 'fg_code', 'fg_description'];
        $sort = in_array($request->string('sort')->toString(), $sortableFields, true)
            ? $request->string('sort')->toString()
            : 'rm_code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $page = max(1, $request->integer('page', 1));
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;
        $rawMaterials = RawMaterialCatalog::all()->values();
        $finishedGoods = FinishedGoodCatalog::all()->values();

        $allSynonims = $rawMaterials->map(function (array $rawMaterial, int $index) use ($finishedGoods) {
            $finishedGood = $finishedGoods[$index % $finishedGoods->count()];

            return [
                'rm_code' => $rawMaterial['code'],
                'rm_description' => $rawMaterial['description'],
                'fg_code' => $finishedGood['code'],
                'fg_description' => $finishedGood['description'],
            ];
        });

        $filteredSynonims = $allSynonims
            ->when($search !== '', fn ($items) => $items->filter(fn ($item) => collect($item)->contains(fn ($value) => str_contains(strtolower((string) $value), strtolower($search)))))
            ->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')
            ->values();

        $synonims = new LengthAwarePaginator(
            $filteredSynonims->forPage($page, $perPage)->values(),
            $filteredSynonims->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->except('page')],
        );

        return compact('synonims', 'search', 'sort', 'direction', 'perPage');
    }

    private function getOptions(): array
    {
        return [
            'rawMaterialOptions' => RawMaterialCatalog::all()->map(fn (array $item) => [
                'code' => $item['code'],
                'description' => $item['description'],
            ])->values(),
            'finishedGoodOptions' => FinishedGoodCatalog::all()->map(fn (array $item) => [
                'code' => $item['code'],
                'description' => $item['description'],
            ])->values(),
        ];
    }
}
