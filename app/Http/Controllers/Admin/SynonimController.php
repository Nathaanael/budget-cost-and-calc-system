<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\SynonimRequest;
use App\Models\FinishedGood;
use App\Models\RawMaterial;
use App\Models\Synonim;
use App\Support\PlantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(SynonimRequest $request): RedirectResponse
    {
        Synonim::create([
            ...$this->relationIds($request),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.maintenance.synonim.index')
            ->with('success', __('Synonim berhasil ditambahkan.'));
    }

    public function update(SynonimRequest $request, Synonim $synonim): JsonResponse
    {
        $synonim->update([
            ...$this->relationIds($request),
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => __('Synonim berhasil diperbarui.'),
            'data' => $this->synonimPayload($synonim->fresh(['rawMaterial', 'finishedGood'])),
        ]);
    }

    public function destroy(Synonim $synonim): JsonResponse
    {
        $synonim->delete();

        return response()->json(['message' => __('Synonim berhasil dihapus.')]);
    }

    private function getSynonims(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sortableFields = [
            'rm_code' => 'raw_materials.code',
            'rm_description' => 'raw_materials.description',
            'fg_code' => 'finished_goods.code',
            'fg_description' => 'finished_goods.description',
        ];
        $sort = array_key_exists($request->string('sort')->toString(), $sortableFields)
            ? $request->string('sort')->toString()
            : 'rm_code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;

        $synonims = Synonim::query()
            ->with(['rawMaterial', 'finishedGood'])
            ->join('raw_materials', 'raw_materials.id', '=', 'synonims.raw_material_id')
            ->join('finished_goods', 'finished_goods.id', '=', 'synonims.finished_good_id')
            ->where('raw_materials.plant_id', app(PlantContext::class)->id())
            ->select('synonims.*')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('raw_materials.code', 'like', "%{$search}%")
                ->orWhere('raw_materials.description', 'like', "%{$search}%")
                ->orWhere('finished_goods.code', 'like', "%{$search}%")
                ->orWhere('finished_goods.description', 'like', "%{$search}%")))
            ->orderBy($sortableFields[$sort], $direction)
            ->when($sort !== 'rm_code', fn ($query) => $query->orderBy('raw_materials.code'))
            ->paginate($perPage)
            ->withQueryString();

        $synonims->setCollection(
            $synonims->getCollection()->map(fn (Synonim $synonim) => $this->synonimPayload($synonim)),
        );

        return compact('synonims', 'search', 'sort', 'direction', 'perPage');
    }

    private function getOptions(): array
    {
        return [
            'rawMaterialOptions' => RawMaterial::query()
                ->orderBy('code')
                ->get(['code', 'description']),
            'finishedGoodOptions' => FinishedGood::withoutGlobalScope('plant')
                ->with('plant:id,code,description')
                ->orderBy('code')
                ->get(['id', 'plant_id', 'code', 'description']),
        ];
    }

    private function relationIds(SynonimRequest $request): array
    {
        return [
            'raw_material_id' => RawMaterial::where('code', $request->validated('rm_code'))->firstOrFail()->id,
            'finished_good_id' => $request->integer('fg_id'),
        ];
    }

    private function synonimPayload(Synonim $synonim): array
    {
        return [
            'id' => $synonim->id,
            'rm_code' => $synonim->rawMaterial->code,
            'rm_description' => $synonim->rawMaterial->description,
            'fg_code' => $synonim->finishedGood->code,
            'fg_description' => $synonim->finishedGood->description,
            'fg_id' => $synonim->finishedGood->id,
            'fg_plant' => $synonim->finishedGood->plant?->code,
        ];
    }
}
