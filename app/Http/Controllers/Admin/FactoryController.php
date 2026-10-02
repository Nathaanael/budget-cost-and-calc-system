<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\FactoryRequest;
use App\Models\AreaNoodle;
use App\Models\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FactoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.factory.factory', [
            'title' => __('Factory'),
            'areaOptions' => $this->areaOptions(),
            ...$this->getFactories($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->paginationPayload($this->getFactories($request)['factories']));
    }

    public function create(): View
    {
        return view('pages.user.maintenance.factory.createFactory', [
            'title' => __('Tambah Factory'),
            'areaOptions' => $this->areaOptions(),
        ]);
    }

    public function store(FactoryRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $factory = Factory::create([
                'code' => $request->validated('code'),
                'description' => $request->validated('description'),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->syncAreas($factory, $request->validated());
        });

        return redirect()
            ->route('admin.maintenance.factory.index')
            ->with('success', __('Factory berhasil ditambahkan.'));
    }

    public function edit(Factory $factory): View
    {
        $factory->load('areaSlots.areaNoodle');

        return view('pages.user.maintenance.factory.editFactory', [
            'title' => __('Edit Factory'),
            'factory' => $factory,
            'factoryData' => $this->factoryPayload($factory),
            'areaOptions' => $this->areaOptions(),
        ]);
    }

    public function update(FactoryRequest $request, Factory $factory): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $factory) {
            $factory->update([
                'code' => $request->validated('code'),
                'description' => $request->validated('description'),
                'updated_by' => $request->user()->id,
            ]);
            $this->syncAreas($factory, $request->validated());
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Factory berhasil diperbarui.'),
                'data' => $this->factoryPayload($factory->fresh('areaSlots.areaNoodle')),
            ]);
        }

        return redirect()
            ->route('admin.maintenance.factory.index')
            ->with('success', __('Factory berhasil diperbarui.'));
    }

    public function destroy(Factory $factory): JsonResponse
    {
        $factory->delete();

        return response()->json(['message' => __('Factory berhasil dihapus.')]);
    }

    private function getFactories(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sort = in_array($request->string('sort')->toString(), ['code', 'description'], true)
            ? $request->string('sort')->toString()
            : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;

        $factories = Factory::query()
            ->with('areaSlots.areaNoodle')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)
            ->when($sort !== 'code', fn ($query) => $query->orderBy('code'))
            ->paginate($perPage)
            ->withQueryString();

        $factories->setCollection(
            $factories->getCollection()->map(fn (Factory $factory) => $this->factoryPayload($factory)),
        );

        return compact('factories', 'search', 'sort', 'direction', 'perPage');
    }

    private function syncAreas(Factory $factory, array $data): void
    {
        $areaCodes = collect(range(1, 10))
            ->mapWithKeys(fn ($position) => [$position => $data["area_{$position}"] ?? null])
            ->filter();
        $areas = AreaNoodle::query()
            ->whereIn('code', $areaCodes->values())
            ->get()
            ->keyBy('code');

        $factory->areaSlots()->delete();

        foreach ($areaCodes as $position => $code) {
            $factory->areaSlots()->create([
                'area_noodle_id' => $areas->get($code)->id,
                'position' => $position,
            ]);
        }
    }

    private function factoryPayload(Factory $factory): array
    {
        $payload = $factory->only(['id', 'code', 'description']);
        $slots = $factory->areaSlots->keyBy('position');

        foreach (range(1, 10) as $position) {
            $payload["area_{$position}"] = $slots->get($position)?->areaNoodle?->code;
        }

        return $payload;
    }

    private function areaOptions()
    {
        return AreaNoodle::query()->orderBy('code')->get(['id', 'code', 'description']);
    }

    private function paginationPayload($paginator): array
    {
        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
        ];
    }
}
