<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RawMaterialRequest;
use App\Models\RawMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RawMaterialController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.rawMaterial.rawMaterial', ['title' => __('Raw Material'), ...$this->getRawMaterials($request)]);
    }

    public function data(Request $request): JsonResponse
    {
        $items = $this->getRawMaterials($request)['rawMaterials'];

        return response()->json(['data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'from' => $items->firstItem() ?? 0, 'to' => $items->lastItem() ?? 0, 'total' => $items->total(), 'per_page' => $items->perPage()]]);
    }

    public function create(): View
    {
        return view('pages.user.maintenance.rawMaterial.createRawMaterial', ['title' => __('Tambah Raw Material')]);
    }

    public function store(RawMaterialRequest $request): RedirectResponse
    {
        RawMaterial::create([...$request->validated(), 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);

        return redirect()->route('admin.maintenance.raw-material.index')->with('success', __('Raw material berhasil ditambahkan.'));
    }

    public function edit(RawMaterial $rawMaterial): View
    {
        return view('pages.user.maintenance.rawMaterial.editRawMaterial', [
            'title' => __('Edit Raw Material'),
            'rawMaterial' => $rawMaterial,
        ]);
    }

    public function update(RawMaterialRequest $request, RawMaterial $rawMaterial): JsonResponse|RedirectResponse
    {
        $rawMaterial->update([...$request->validated(), 'updated_by' => $request->user()->id]);

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Raw material berhasil diperbarui.'), 'data' => $rawMaterial->fresh()]);
        }

        return redirect()->route('admin.maintenance.raw-material.index')->with('success', __('Raw material berhasil diperbarui.'));
    }

    public function destroy(RawMaterial $rawMaterial): JsonResponse
    {
        $rawMaterial->delete();

        return response()->json(['message' => __('Raw material berhasil dihapus.')]);
    }

    private function getRawMaterials(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $selectedCurrency = $request->string('currency_type')->toString();
        $sortableFields = ['code', 'description', 'unit', 'wastage_all', 'material_id', 'currency_type', 'type_rm'];
        $sort = in_array($request->string('sort')->toString(), $sortableFields, true) ? $request->string('sort')->toString() : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true) ? $request->integer('per_page') : 5;
        $rawMaterials = RawMaterial::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere('material_id', 'like', "%{$search}%")))
            ->when(in_array($selectedCurrency, ['Rp', 'USD'], true), fn ($query) => $query->where('currency_type', $selectedCurrency))
            ->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return compact('rawMaterials', 'search', 'selectedCurrency', 'sort', 'direction', 'perPage');
    }
}
