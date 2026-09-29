<?php

namespace App\Http\Controllers\Admin\Entry;

use App\Http\Controllers\Controller;
use App\Support\AreaNoodleCatalog;
use App\Support\NoodleCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class VolumeNoodleController extends Controller
{
    private const LE_FIELDS = ['le_june', 'le_august', 'le_september', 'le_october', 'le_november', 'le_december'];

    private const MONTH_FIELDS = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

    public function index(Request $request): View
    {
        return view('pages.user.entry.volumeNoodle.volumeNoodle', [
            'title' => __('Volume Noodle'),
            ...$this->getVolumes($request),
            ...$this->getOptions(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $volumes = $this->getVolumes($request)['volumes'];

        return response()->json([
            'data' => $volumes->items(),
            'meta' => [
                'current_page' => $volumes->currentPage(),
                'last_page' => $volumes->lastPage(),
                'from' => $volumes->firstItem() ?? 0,
                'to' => $volumes->lastItem() ?? 0,
                'total' => $volumes->total(),
                'per_page' => $volumes->perPage(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.user.entry.volumeNoodle.createVolumeNdl', [
            'title' => __('Tambah Volume Noodle'),
            ...$this->getOptions(),
        ]);
    }

    private function getVolumes(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sortableFields = ['area_code', 'noodle_code', ...self::LE_FIELDS, 'total_le', ...self::MONTH_FIELDS, 'total_aop'];
        $sort = in_array($request->string('sort')->toString(), $sortableFields, true)
            ? $request->string('sort')->toString()
            : 'area_code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $page = max(1, $request->integer('page', 1));
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;
        $areas = AreaNoodleCatalog::all()->values();
        $noodles = NoodleCatalog::all()->values();

        $allVolumes = collect(range(1, 10))->map(function (int $sequence) use ($areas, $noodles) {
            $area = $areas[($sequence - 1) % $areas->count()];
            $noodle = $noodles[$sequence - 1];
            $volume = [
                'area_code' => $area['code'],
                'area_description' => $area['description'],
                'noodle_code' => $noodle['code'],
                'noodle_description' => $noodle['description'],
            ];

            foreach (self::LE_FIELDS as $index => $field) {
                $volume[$field] = 900 + ($sequence * 50) + ($index * 25);
            }

            foreach (self::MONTH_FIELDS as $index => $field) {
                $volume[$field] = 1000 + ($sequence * 100) + ($index * 20);
            }

            $volume['total_le'] = collect(self::LE_FIELDS)->sum(fn (string $field) => $volume[$field]);
            $volume['total_aop'] = collect(self::MONTH_FIELDS)->sum(fn (string $field) => $volume[$field]);

            return $volume;
        });

        $filteredVolumes = $allVolumes
            ->when($search !== '', fn ($items) => $items->filter(fn ($item) => str_contains(strtolower($item['area_code']), strtolower($search))
                || str_contains(strtolower($item['area_description']), strtolower($search))
                || str_contains(strtolower($item['noodle_code']), strtolower($search))
                || str_contains(strtolower($item['noodle_description']), strtolower($search))))
            ->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')
            ->values();

        $volumes = new LengthAwarePaginator(
            $filteredVolumes->forPage($page, $perPage)->values(),
            $filteredVolumes->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->except('page')],
        );

        return compact('volumes', 'search', 'sort', 'direction', 'perPage');
    }

    private function getOptions(): array
    {
        return [
            'areaOptions' => AreaNoodleCatalog::all(),
            'noodleOptions' => NoodleCatalog::all(),
        ];
    }
}
