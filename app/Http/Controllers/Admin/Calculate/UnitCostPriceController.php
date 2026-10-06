<?php

namespace App\Http\Controllers\Admin\Calculate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calculate\UnitCostPriceRequest;
use App\Models\FinishedGood;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UnitCostPriceController extends Controller
{
    private const PERIODS = ['current', 'le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'];

    private const FACTORIES = [
        'cikampek' => ['pe' => 'pe_cikampek', 'price_prefix' => 'unit_price'],
        'semarang' => ['pe' => 'pe_semarang', 'price_prefix' => 'unit_price_semarang'],
        'surabaya' => ['pe' => 'pe_surabaya', 'price_prefix' => 'unit_price_surabaya'],
        'palembang' => ['pe' => 'pe_palembang', 'price_prefix' => 'unit_price_palembang'],
    ];

    public function index(Request $request): View
    {
        return view('pages.user.calculate.uCostUPrice.uCostUPrice', [
            'title' => __('U.Cost+U.Price'),
            'table' => $this->tableData($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->tableData($request));
    }

    public function store(UnitCostPriceRequest $request): JsonResponse
    {
        $calculateMultiLevel = $request->boolean('calculate_multi_level');
        $userId = $request->user()->id;
        $selectedIds = $request->validated('finished_good_ids');

        $summary = DB::transaction(function () use ($calculateMultiLevel, $userId, $selectedIds): array {
            $summary = [
                'calculated_finished_goods' => 0,
                'skipped_finished_goods' => 0,
                'updated_periods' => 0,
                'missing_prices' => 0,
                'propagated_materials' => 0,
            ];

            if ($calculateMultiLevel) {
                $multiLevelGoods = $this->finishedGoodsForCalculation(true, $selectedIds);
                $this->calculateFinishedGoods($multiLevelGoods, $userId, $summary);
                $summary['propagated_materials'] = $this->propagateMultiLevelPrices(
                    $multiLevelGoods->filter(fn ($good) => $good->rawMaterialFormula?->items->isNotEmpty()), $userId
                );
            }

            $regularGoods = $this->finishedGoodsForCalculation(false, $selectedIds);
            $this->calculateFinishedGoods($regularGoods, $userId, $summary);

            return $summary;
        });

        return response()->json([
            'message' => $summary['calculated_finished_goods'] > 0
                ? __('Unit Cost dan Unit Price berhasil dihitung dan disimpan.')
                : __('No FG calculated. Selected FG have no formula.'),
            'summary' => $summary,
        ]);
    }

    private function tableData(Request $request): array
    {
        $query = FinishedGood::query();
        $search = trim((string) $request->query('search', ''));

        if (! $request->boolean('calc_multi', true)) {
            $query->where('multi_level', '!=', 'Y');
        }

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $formulaReady = (clone $query)->whereHas('rawMaterialFormula.items')->count();
        $multiLevel = (clone $query)->where('multi_level', 'Y')->count();
        $paginator = $query
            ->withCount(['rawMaterialFormula as formula_items_count' => fn (Builder $query) => $query
                ->join('finished_good_formula_items', 'finished_good_formula_items.finished_good_formula_id', '=', 'finished_good_formulas.id')
                ->whereNull('finished_good_formula_items.deleted_at')])
            ->orderByRaw("CASE WHEN multi_level = 'Y' THEN 0 ELSE 1 END")
            ->orderBy('code')
            ->paginate(5)
            ->withQueryString();

        return [
            'data' => $paginator->getCollection()->values(),
            'meta' => $this->paginationMeta($paginator),
            'summary' => [
                'total' => $total,
                'formula_ready' => $formulaReady,
                'missing_formula' => $total - $formulaReady,
                'multi_level' => $multiLevel,
            ],
        ];
    }

    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    private function finishedGoodsForCalculation(bool $multiLevel, array $selectedIds): Collection
    {
        return FinishedGood::query()
            ->whereIn('id', $selectedIds)
            ->with(['rawMaterialFormula.items.rawMaterial'])
            ->when(! $multiLevel, fn (Builder $query) => $query->where('multi_level', '!=', 'Y'))
            ->when($multiLevel, fn (Builder $query) => $query->where('multi_level', 'Y'))
            ->orderBy('code')
            ->lockForUpdate()
            ->get();
    }

    private function calculateFinishedGoods(Collection $finishedGoods, int $userId, array &$summary): void
    {
        $rawMaterialIds = $finishedGoods
            ->flatMap(fn (FinishedGood $finishedGood) => $finishedGood->rawMaterialFormula?->items->pluck('raw_material_id') ?? collect())
            ->unique()
            ->values();
        $prices = RawMaterialPrice::query()
            ->whereIn('raw_material_id', $rawMaterialIds)
            ->whereIn('period', self::PERIODS)
            ->lockForUpdate()
            ->get()
            ->groupBy('raw_material_id')
            ->map(fn (Collection $prices) => $prices->keyBy('period'));

        foreach ($finishedGoods as $finishedGood) {
            $items = $finishedGood->rawMaterialFormula?->items;

            if (! $items || $items->isEmpty()) {
                $summary['skipped_finished_goods']++;

                continue;
            }

            $values = ['updated_by' => $userId];

            foreach (self::PERIODS as $period) {
                $unitCost = 0.0;

                foreach ($items as $item) {
                    $rawMaterial = $item->rawMaterial;
                    $price = $rawMaterial ? $prices->get($rawMaterial->id)?->get($period) : null;

                    if (! $rawMaterial || ! $price) {
                        $summary['missing_prices']++;

                        continue;
                    }

                    $standardWithWaste = (float) $item->standard * (1 + (float) $rawMaterial->wastage_all);
                    $quantity = trim((string) $rawMaterial->material_id) === ''
                        ? $standardWithWaste / 1000
                        : $standardWithWaste;
                    $unitCost += $quantity * (float) $price->rupiah_amount;
                }

                $unitCost = round($unitCost, 2);
                $values["unit_cost_{$period}"] = $unitCost;

                // Calc3 lama hanya menimpa Unit Price ketika Unit Cost tidak nol.
                if ($unitCost != 0.0) {
                    foreach (self::FACTORIES as $factory) {
                        $values["{$factory['price_prefix']}_{$period}"] = round(
                            $unitCost + (float) $finishedGood->{$factory['pe']},
                            2
                        );
                    }

                    $summary['updated_periods']++;
                }
            }

            $finishedGood->update($values);
            $summary['calculated_finished_goods']++;
        }
    }

    private function propagateMultiLevelPrices(Collection $multiLevelGoods, int $userId): int
    {
        if ($multiLevelGoods->isEmpty()) {
            return 0;
        }

        $goodsByCode = $multiLevelGoods->keyBy('code');
        $materials = RawMaterial::query()
            ->whereIn('code', $goodsByCode->keys())
            ->lockForUpdate()
            ->get();

        RawMaterialPrice::query()
            ->whereIn('raw_material_id', $materials->pluck('id'))
            ->lockForUpdate()
            ->get();

        foreach ($materials as $material) {
            $finishedGood = $goodsByCode->get($material->code);

            foreach (self::PERIODS as $period) {
                $price = RawMaterialPrice::firstOrNew([
                    'raw_material_id' => $material->id,
                    'period' => $period,
                ]);

                if (! $price->exists) {
                    $price->created_by = $userId;
                    $price->usd_amount = 0;
                }

                $price->fill([
                    'rupiah_amount' => (float) $finishedGood->{"unit_price_{$period}"},
                    'source_kind' => 'multi_level',
                    'reference_id' => null,
                    'exchange_rate' => null,
                    'calculated_at' => now(),
                    'updated_by' => $userId,
                ])->save();
            }
        }

        return $materials->count();
    }
}
