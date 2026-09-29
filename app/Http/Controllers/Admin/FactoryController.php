<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AreaNoodleCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class FactoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.factory.factory', [
            'title' => __('Factory'),
            'areaOptions' => AreaNoodleCatalog::all(),
            ...$this->getFactories($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $factories = $this->getFactories($request)['factories'];

        return response()->json([
            'data' => $factories->items(),
            'meta' => [
                'current_page' => $factories->currentPage(),
                'last_page' => $factories->lastPage(),
                'from' => $factories->firstItem() ?? 0,
                'to' => $factories->lastItem() ?? 0,
                'total' => $factories->total(),
                'per_page' => $factories->perPage(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.user.maintenance.factory.createFactory', [
            'title' => __('Tambah Factory'),
            'areaOptions' => AreaNoodleCatalog::all(),
        ]);
    }

    private function getFactories(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sort = in_array($request->string('sort')->toString(), ['code', 'description'], true)
            ? $request->string('sort')->toString()
            : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $page = max(1, $request->integer('page', 1));
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;
        $areaCodes = AreaNoodleCatalog::all()->pluck('code')->values();

        $allFactories = collect(range(1, 10))->map(function (int $sequence) use ($areaCodes) {
            $factory = [
                'code' => 'F'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
                'description' => 'FACTORY '.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
            ];

            foreach (range(1, 10) as $position) {
                $factory['area_'.$position] = $areaCodes[($sequence + $position - 2) % $areaCodes->count()];
            }

            return $factory;
        });

        $filteredFactories = $allFactories
            ->when($search !== '', fn ($items) => $items->filter(fn ($item) => str_contains(strtolower($item['code']), strtolower($search))
                || str_contains(strtolower($item['description']), strtolower($search))))
            ->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')
            ->values();

        $factories = new LengthAwarePaginator(
            $filteredFactories->forPage($page, $perPage)->values(),
            $filteredFactories->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->except('page')],
        );

        return compact('factories', 'search', 'sort', 'direction', 'perPage');
    }
}
