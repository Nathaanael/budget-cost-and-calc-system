<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class NoodleController extends Controller
{
    public function index(Request $request): View
    {
        $result = $this->getNoodles($request);

        return view('pages.user.maintenance.noodle.noodle', [
            'title' => __('Noodle'),
            ...$result,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $result = $this->getNoodles($request);
        $noodles = $result['noodles'];

        return response()->json([
            'data' => $noodles->items(),
            'meta' => [
                'current_page' => $noodles->currentPage(),
                'last_page' => $noodles->lastPage(),
                'from' => $noodles->firstItem() ?? 0,
                'to' => $noodles->lastItem() ?? 0,
                'total' => $noodles->total(),
                'per_page' => $noodles->perPage(),
            ],
        ]);
    }

    private function getNoodles(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $selectedUnit = $request->string('unit')->toString();
        $sort = in_array($request->string('sort')->toString(), ['code', 'description', 'unit'], true)
            ? $request->string('sort')->toString()
            : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $page = max(1, $request->integer('page', 1));
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;

        $allNoodles = collect([
            ['code' => '2000001', 'description' => 'Indomie Mi Goreng', 'unit' => 'Dus'],
            ['code' => '2000002', 'description' => 'Indomie Rasa Ayam Bawang', 'unit' => 'Dus'],
            ['code' => '2000003', 'description' => 'Supermi Rasa Ayam Bawang', 'unit' => 'Dus'],
            ['code' => '2000004', 'description' => 'Sarimi Isi 2 Mi Goreng', 'unit' => 'Dus'],
            ['code' => '2000005', 'description' => 'Pop Mie Rasa Ayam', 'unit' => 'Cup'],
            ['code' => '2000006', 'description' => 'Indomie Mi Goreng', 'unit' => 'Dus'],
            ['code' => '2000007', 'description' => 'Indomie Rasa Ayam Bawang', 'unit' => 'Dus'],
            ['code' => '2000008', 'description' => 'Supermi Rasa Ayam Bawang', 'unit' => 'Dus'],
            ['code' => '2000009', 'description' => 'Sarimi Isi 2 Mi Goreng', 'unit' => 'Dus'],
            ['code' => '2000010', 'description' => 'Pop Mie Rasa Ayam', 'unit' => 'Cup'],
        ]);

        $units = $allNoodles->pluck('unit')->unique()->sort()->values();
        $filteredNoodles = $allNoodles
            ->when($search !== '', function ($noodles) use ($search) {
                return $noodles->filter(function ($noodle) use ($search) {
                    return str_contains(strtolower($noodle['code']), strtolower($search))
                        || str_contains(strtolower($noodle['description']), strtolower($search));
                });
            })
            ->when($selectedUnit !== '', fn ($noodles) => $noodles->where('unit', $selectedUnit))
            ->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')
            ->values();

        $noodles = new LengthAwarePaginator(
            $filteredNoodles->forPage($page, $perPage)->values(),
            $filteredNoodles->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ],
        );

        return [
            'noodles' => $noodles,
            'search' => $search,
            'selectedUnit' => $selectedUnit,
            'units' => $units,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ];
    }

    public function create(): View
    {
        return view('pages.user.maintenance.noodle.createNoodle', [
            'title' => __('Tambah Noodle'),
        ]);
    }
}
