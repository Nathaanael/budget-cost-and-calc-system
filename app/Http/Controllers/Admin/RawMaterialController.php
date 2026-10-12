<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RawMaterialRequest;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use Illuminate\Database\QueryException;
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

            if ($this->hasPriceValues($validated)) {
                $this->savePrices($rawMaterial, $validated, $request->user()->id);
            }
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

            if ($this->hasPriceValues($validated)) {
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
        $usages = [];

        if ($rawMaterial->finishedGoodFormulaItems()->exists()) {
            $usages[] = __('Formula FG');
        }

        if ($rawMaterial->synonim()->exists()) {
            $usages[] = __('Synonim');
        }

        if ($usages !== []) {
            return response()->json([
                'message' => __('Raw Material :code tidak dapat dihapus karena masih digunakan pada: :usages. Hapus relasinya terlebih dahulu.', [
                    'code' => $rawMaterial->code,
                    'usages' => implode(', ', $usages),
                ]),
            ], 422);
        }

        try {
            $rawMaterial->delete();
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['19', '23000'], true)) {
                throw $exception;
            }

            return response()->json([
                'message' => __('Raw Material tidak dapat dihapus karena masih digunakan oleh data lain.'),
            ], 422);
        }

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
            $usdField = "usd_{$period}";
            $rupiahField = "rupiah_{$period}";

            if (($validated[$usdField] ?? null) === null && ($validated[$rupiahField] ?? null) === null) {
                continue;
            }

            $price = RawMaterialPrice::firstOrNew([
                'raw_material_id' => $rawMaterial->id,
                'period' => $period,
            ]);

            if (! $price->exists) {
                $price->created_by = $userId;
            }

            $price->fill([
                'usd_amount' => $validated[$usdField] ?? ($price->exists ? $price->usd_amount : 0),
                'rupiah_amount' => $validated[$rupiahField] ?? ($price->exists ? $price->rupiah_amount : 0),
                'source_kind' => 'manual',
                'reference_id' => null,
                'exchange_rate' => null,
                'calculated_at' => null,
                'updated_by' => $userId,
            ])->save();
        }
    }

    private function hasPriceValues(array $validated): bool
    {
        foreach (self::PRICE_FIELDS as $field) {
            if (($validated[$field] ?? null) !== null) {
                return true;
            }
        }

        return false;
    }
}
