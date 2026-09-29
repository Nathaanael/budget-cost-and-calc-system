<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\RawMaterialCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class RawMaterialController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.rawMaterial.rawMaterial', [
            'title' => __('Raw Material'),
            ...$this->getRawMaterials($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $rawMaterials = $this->getRawMaterials($request)['rawMaterials'];

        return response()->json([
            'data' => $rawMaterials->items(),
            'meta' => [
                'current_page' => $rawMaterials->currentPage(),
                'last_page' => $rawMaterials->lastPage(),
                'from' => $rawMaterials->firstItem() ?? 0,
                'to' => $rawMaterials->lastItem() ?? 0,
                'total' => $rawMaterials->total(),
                'per_page' => $rawMaterials->perPage(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.user.maintenance.rawMaterial.createRawMaterial', [
            'title' => __('Tambah Raw Material'),
        ]);
    }

    private function getRawMaterials(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $selectedCurrency = $request->string('currency_type')->toString();
        $sortableFields = ['code', 'description', 'unit', 'wastage_all', 'material_id', 'currency_type', 'type_rm'];
        $sort = in_array($request->string('sort')->toString(), $sortableFields, true)
            ? $request->string('sort')->toString()
            : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $page = max(1, $request->integer('page', 1));
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;

        $allRawMaterials = RawMaterialCatalog::all();

        $filteredRawMaterials = $allRawMaterials
            ->when($search !== '', function ($items) use ($search) {
                return $items->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['code']), strtolower($search))
                        || str_contains(strtolower($item['description']), strtolower($search))
                        || str_contains(strtolower($item['material_id']), strtolower($search));
                });
            })
            ->when(in_array($selectedCurrency, ['Rp', 'USD'], true), fn ($items) => $items->where('currency_type', $selectedCurrency))
            ->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')
            ->values();

        $rawMaterials = new LengthAwarePaginator(
            $filteredRawMaterials->forPage($page, $perPage)->values(),
            $filteredRawMaterials->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->except('page')],
        );

        return compact('rawMaterials', 'search', 'selectedCurrency', 'sort', 'direction', 'perPage');
    }
}
