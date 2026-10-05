<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RawMaterialRequest;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RawMaterialController extends Controller
{
    private const PRICE_FIELDS = [
        'usd_current', 'rupiah_current', 'usd_le', 'rupiah_le',
        'usd_qtr_1', 'rupiah_qtr_1', 'usd_qtr_2', 'rupiah_qtr_2',
        'usd_qtr_3', 'rupiah_qtr_3', 'usd_qtr_4', 'rupiah_qtr_4',
    ];

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
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated): void {
            $rawMaterial = RawMaterial::create([
                ...Arr::except($validated, self::PRICE_FIELDS),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->savePrices($rawMaterial, $validated, $request->user()->id);
        });

        return redirect()->route('admin.maintenance.raw-material.index')->with('success', __('Raw material berhasil ditambahkan.'));
    }

    public function edit(RawMaterial $rawMaterial): View
    {
        $rawMaterial->load('prices');

        return view('pages.user.maintenance.rawMaterial.editRawMaterial', [
            'title' => __('Edit Raw Material'),
            'rawMaterial' => $rawMaterial,
        ]);
    }

    public function update(RawMaterialRequest $request, RawMaterial $rawMaterial): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated, $rawMaterial): void {
            $rawMaterial->update([
                ...Arr::except($validated, self::PRICE_FIELDS),
                'updated_by' => $request->user()->id,
            ]);

            if ($request->hasAny(self::PRICE_FIELDS)) {
                $this->savePrices($rawMaterial, $validated, $request->user()->id);
            }
        });

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
            ->with('prices')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere('material_id', 'like', "%{$search}%")))
            ->when(in_array($selectedCurrency, ['Rp', 'USD'], true), fn ($query) => $query->where('currency_type', $selectedCurrency))
            ->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return compact('rawMaterials', 'search', 'selectedCurrency', 'sort', 'direction', 'perPage');
    }

    private function savePrices(RawMaterial $rawMaterial, array $validated, int $userId): void
    {
        foreach (RawMaterialPrice::PERIODS as $period) {
            $price = RawMaterialPrice::firstOrNew([
                'raw_material_id' => $rawMaterial->id,
                'period' => $period,
            ]);

            if (! $price->exists) {
                $price->created_by = $userId;
            }

            $price->fill([
                'usd_amount' => $validated["usd_{$period}"] ?? 0,
                'rupiah_amount' => $validated["rupiah_{$period}"] ?? 0,
                'source_kind' => 'manual',
                'reference_id' => null,
                'exchange_rate' => null,
                'calculated_at' => null,
                'updated_by' => $userId,
            ])->save();
        }
    }
}
