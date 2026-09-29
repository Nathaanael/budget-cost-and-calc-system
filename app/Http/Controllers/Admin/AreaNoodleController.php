<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AreaNoodleCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AreaNoodleController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.areaNoodle.areaNoodle', [
            'title' => __('Area Noodle'),
            ...$this->getAreas($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $areas = $this->getAreas($request)['areas'];

        return response()->json([
            'data' => $areas->items(),
            'meta' => [
                'current_page' => $areas->currentPage(),
                'last_page' => $areas->lastPage(),
                'from' => $areas->firstItem() ?? 0,
                'to' => $areas->lastItem() ?? 0,
                'total' => $areas->total(),
                'per_page' => $areas->perPage(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.user.maintenance.areaNoodle.createAreaNoodle', [
            'title' => __('Tambah Area Noodle'),
        ]);
    }

    private function getAreas(Request $request): array
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

        $allAreas = AreaNoodleCatalog::all();

        $filteredAreas = $allAreas
            ->when($search !== '', function ($areas) use ($search) {
                return $areas->filter(function ($area) use ($search) {
                    return str_contains(strtolower($area['code']), strtolower($search))
                        || str_contains(strtolower($area['description']), strtolower($search));
                });
            })
            ->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')
            ->values();

        $areas = new LengthAwarePaginator(
            $filteredAreas->forPage($page, $perPage)->values(),
            $filteredAreas->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ],
        );

        return compact('areas', 'search', 'sort', 'direction', 'perPage');
    }
}
