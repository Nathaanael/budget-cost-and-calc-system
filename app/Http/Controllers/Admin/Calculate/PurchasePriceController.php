<?php

namespace App\Http\Controllers\Admin\Calculate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calculate\PurchasePriceRequest;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use App\Models\Reference;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchasePriceController extends Controller
{
    private const RATE_FIELDS = [
        'current' => 'rate_current',
        'le' => 'rate_le',
        'qtr_1' => 'rate_1',
        'qtr_2' => 'rate_2',
        'qtr_3' => 'rate_3',
        'qtr_4' => 'rate_4',
    ];

    public function index(): View
    {
        return view('pages.user.calculate.purchasePrice', [
            'title' => __('Purchase Price'),
            'references' => Reference::query()
                ->orderBy('code')
                ->get([
                    'id', 'code', 'description_1', 'description_2', 'period', 'period_description',
                    'rate_current', 'rate_le', 'rate_1', 'rate_2', 'rate_3', 'rate_4',
                ]),
            'rawMaterials' => $this->rawMaterials(),
        ]);
    }

    public function store(PurchasePriceRequest $request): JsonResponse
    {
        $reference = Reference::findOrFail($request->validated('reference_id'));
        $rates = collect(self::RATE_FIELDS)
            ->mapWithKeys(fn (string $field, string $period) => [$period => (float) $reference->{$field}]);
        $calculatedAt = now();

        $summary = DB::transaction(function () use ($reference, $rates, $calculatedAt, $request): array {
            $materials = RawMaterial::query()
                ->where('currency_type', 'USD')
                ->whereIn('id', $request->validated('raw_material_ids'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);
            $pricesByMaterial = RawMaterialPrice::query()
                ->whereIn('raw_material_id', $materials->pluck('id'))
                ->whereIn('period', RawMaterialPrice::PERIODS)
                ->lockForUpdate()
                ->get()
                ->groupBy('raw_material_id');
            $updatedMaterials = 0;
            $updatedPrices = 0;
            $skippedPrices = 0;

            foreach ($materials as $material) {
                $materialUpdated = false;
                $prices = $pricesByMaterial->get($material->id, collect())->keyBy('period');

                foreach (RawMaterialPrice::PERIODS as $period) {
                    $price = $prices->get($period);
                    $rate = $rates->get($period, 0);

                    if (! $price || $price->usd_amount <= 0 || $rate <= 0) {
                        $skippedPrices++;

                        continue;
                    }

                    $price->update([
                        'rupiah_amount' => round($price->usd_amount * $rate, 2),
                        'source_kind' => 'fx',
                        'reference_id' => $reference->id,
                        'exchange_rate' => $rate,
                        'calculated_at' => $calculatedAt,
                        'updated_by' => $request->user()->id,
                    ]);

                    $materialUpdated = true;
                    $updatedPrices++;
                }

                if ($materialUpdated) {
                    $updatedMaterials++;
                }
            }

            return [
                'total_materials' => $materials->count(),
                'updated_materials' => $updatedMaterials,
                'updated_prices' => $updatedPrices,
                'skipped_prices' => $skippedPrices,
            ];
        });

        return response()->json([
            'message' => __('Purchase Price berhasil dihitung dan disimpan.'),
            'summary' => $summary,
            'raw_materials' => $this->rawMaterials(),
        ]);
    }

    private function rawMaterials(): Collection
    {
        return RawMaterial::query()
            ->where('currency_type', 'USD')
            ->with('prices')
            ->orderBy('code')
            ->get(['id', 'code', 'description', 'material_id', 'currency_type'])
            ->map(function (RawMaterial $rawMaterial): array {
                $prices = $rawMaterial->prices->keyBy('period');
                $periods = [];

                foreach (RawMaterialPrice::PERIODS as $period) {
                    $periods[$period] = [
                        'usd' => $prices->get($period)?->usd_amount ?? 0,
                        'rupiah' => $prices->get($period)?->rupiah_amount ?? 0,
                    ];
                }

                return [
                    ...$rawMaterial->only(['id', 'code', 'description', 'material_id', 'currency_type']),
                    'prices' => $periods,
                ];
            });
    }
}
