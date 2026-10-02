<?php

namespace App\Http\Controllers\Admin\Entry;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entry\RmPriceRequest;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RmPriceController extends Controller
{
    public function index(): View
    {
        return view('pages.user.entry.rmPrice.rmPrice', [
            'title' => __('RM Price'),
            'rawMaterials' => RawMaterial::query()
                ->orderBy('code')
                ->get(['id', 'code', 'description', 'material_id', 'currency_type', 'type_rm']),
        ]);
    }

    public function show(RawMaterial $rawMaterial): JsonResponse
    {
        return response()->json($this->payload($rawMaterial));
    }

    public function store(RmPriceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $rawMaterial = RawMaterial::findOrFail($validated['raw_material_id']);
        $groupCode = trim($rawMaterial->material_id);
        $targets = in_array($groupCode, ['', '*'], true)
            ? collect([$rawMaterial])
            : RawMaterial::query()->where('material_id', $rawMaterial->material_id)->get();

        DB::transaction(function () use ($validated, $targets, $request): void {
            foreach ($targets as $target) {
                foreach (RawMaterialPrice::PERIODS as $period) {
                    $price = RawMaterialPrice::firstOrNew([
                        'raw_material_id' => $target->id,
                        'period' => $period,
                    ]);

                    if (! $price->exists) {
                        $price->created_by = $request->user()->id;
                    }

                    $price->fill([
                        'usd_amount' => $validated["usd_{$period}"],
                        'rupiah_amount' => $validated["rupiah_{$period}"],
                        'source_kind' => 'manual',
                        'updated_by' => $request->user()->id,
                    ])->save();
                }
            }
        });

        return response()->json([
            'message' => __('Harga raw material berhasil disimpan.'),
            ...$this->payload($rawMaterial->fresh()),
        ]);
    }

    private function payload(RawMaterial $rawMaterial): array
    {
        $prices = $rawMaterial->prices()->get()->keyBy('period');
        $values = [];

        foreach (RawMaterialPrice::PERIODS as $period) {
            $values["usd_{$period}"] = $prices->get($period)?->usd_amount ?? 0;
            $values["rupiah_{$period}"] = $prices->get($period)?->rupiah_amount ?? 0;
        }

        return [
            'raw_material' => $rawMaterial->only(['id', 'code', 'description', 'material_id', 'currency_type', 'type_rm']),
            'prices' => $values,
        ];
    }
}
