<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\FinishedGoodCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class FinishedGoodController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.finishedGood.finishedGood', [
            'title' => __('Finished Good'),
            ...$this->getFinishedGoods($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $finishedGoods = $this->getFinishedGoods($request)['finishedGoods'];

        return response()->json([
            'data' => $finishedGoods->items(),
            'meta' => [
                'current_page' => $finishedGoods->currentPage(),
                'last_page' => $finishedGoods->lastPage(),
                'from' => $finishedGoods->firstItem() ?? 0,
                'to' => $finishedGoods->lastItem() ?? 0,
                'total' => $finishedGoods->total(),
                'per_page' => $finishedGoods->perPage(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.user.maintenance.finishedGood.createFinishedGood', [
            'title' => __('Tambah Finished Good'),
        ]);
    }

    private function getFinishedGoods(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sortableFields = [
            'code', 'description', 'description_1', 'product_type_1', 'product_type_2',
            'batch', 'selling_price', 'multi_level', 'active',
            'unit_cost_current', 'unit_price_current', 'unit_cost_le', 'unit_price_le',
            'unit_cost_qtr_1', 'unit_price_qtr_1', 'unit_cost_qtr_2', 'unit_price_qtr_2',
            'unit_cost_qtr_3', 'unit_price_qtr_3', 'unit_cost_qtr_4', 'unit_price_qtr_4',
        ];
        $sort = in_array($request->string('sort')->toString(), $sortableFields, true)
            ? $request->string('sort')->toString()
            : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $page = max(1, $request->integer('page', 1));
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;

        $allFinishedGoods = FinishedGoodCatalog::all();

        $filteredFinishedGoods = $allFinishedGoods
            ->when($search !== '', function ($items) use ($search) {
                return $items->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['code']), strtolower($search))
                        || str_contains(strtolower($item['description']), strtolower($search))
                        || str_contains(strtolower($item['description_1']), strtolower($search));
                });
            })
            ->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')
            ->values();

        $finishedGoods = new LengthAwarePaginator(
            $filteredFinishedGoods->forPage($page, $perPage)->values(),
            $filteredFinishedGoods->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ],
        );

        return compact('finishedGoods', 'search', 'sort', 'direction', 'perPage');
    }
}
