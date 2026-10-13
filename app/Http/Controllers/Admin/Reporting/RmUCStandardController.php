<?php

namespace App\Http\Controllers\Admin\Reporting;

use App\Exports\RmUCStandardWorkbook;
use App\Http\Controllers\Controller;
use App\Models\FinishedGood;
use App\Models\FinishedGoodFormulaItem;
use App\Models\RawMaterialPrice;
use App\Models\Reference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Port Report2() di REP1.PRG: "RAW MATERIAL UNIT COST STANDARD".
 */
class RmUCStandardController extends Controller
{
    private const EXPORT_BATCH_SIZE = 25;

    private const EXPORT_MAX_BATCHES = 2000;

    public function index(Request $request): View
    {
        $references = $this->references();
        $filters = $this->filters($request);
        $finishedGoods = $this->finishedGoods($filters)
            ->paginate(5)
            ->withQueryString();

        $finishedGoods->setCollection(
            $finishedGoods->getCollection()->map(fn (FinishedGood $finishedGood) => $this->report($finishedGood))
        );

        return view('pages.user.reporting.rm-uc-standard', [
            'title' => __('RM Unit Cost Standard'),
            'references' => $references,
            'selectedReference' => $this->selectedReference($request, $references),
            'finishedGoods' => $finishedGoods,
            'filters' => $filters,
        ]);
    }

    /**
     * Unduh Excel bertahap (plan → batch → excel) agar halaman bisa menampilkan progress bar
     * dan tidak terlihat macet saat rentang FG besar.
     * Tahap 1: daftar FG sesuai filter dan token untuk menampung hasil batch.
     */
    public function excelPlan(Request $request): JsonResponse
    {
        return response()->json([
            'token' => Str::random(32),
            'finished_good_ids' => $this->finishedGoods($this->filters($request))->pluck('id')->values(),
            'batch_size' => self::EXPORT_BATCH_SIZE,
        ]);
    }

    /**
     * Tahap 2: hitung satu kelompok FG dan simpan hasilnya sementara di cache.
     */
    public function excelBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'alpha_num', 'size:32'],
            'index' => ['required', 'integer', 'min:0', 'max:'.(self::EXPORT_MAX_BATCHES - 1)],
            'finished_good_ids' => ['required', 'array', 'min:1', 'max:'.self::EXPORT_BATCH_SIZE],
            'finished_good_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $reports = $this->reportQuery()
            ->whereIn('id', $validated['finished_good_ids'])
            ->get()
            ->map(fn (FinishedGood $finishedGood) => $this->report($finishedGood))
            ->all();

        Cache::put(
            $this->exportCacheKey($request, $validated['token'], (int) $validated['index']),
            $reports,
            now()->addMinutes(30)
        );

        return response()->json(['processed' => count($reports)]);
    }

    /**
     * Tahap 3: susun file Excel dari seluruh batch lalu hapus cache-nya.
     */
    public function excel(Request $request, RmUCStandardWorkbook $workbook): Response
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'alpha_num', 'size:32'],
            'batches' => ['required', 'integer', 'min:0', 'max:'.self::EXPORT_MAX_BATCHES],
        ]);
        $finishedGoods = collect();

        for ($index = 0; $index < (int) $validated['batches']; $index++) {
            $reports = Cache::get($this->exportCacheKey($request, $validated['token'], $index));

            if ($reports === null) {
                abort(422, __('Data unduhan sudah kedaluwarsa. Silakan unduh ulang.'));
            }

            $finishedGoods = $finishedGoods->merge($reports);
        }

        $references = $this->references();
        $selectedReference = $this->selectedReference($request, $references);
        $period = optional($selectedReference)->period_description ?: now()->format('Y-m-d');
        $filename = 'unit-cost-standar-rm-'.Str::slug($period).'.xlsx';
        $contents = $workbook->make($selectedReference, $finishedGoods, $this->filters($request));

        for ($index = 0; $index < (int) $validated['batches']; $index++) {
            Cache::forget($this->exportCacheKey($request, $validated['token'], $index));
        }

        return response($contents, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function exportCacheKey(Request $request, string $token, int $index): string
    {
        return "rm-uc-standard-export:{$request->user()->id}:{$token}:{$index}";
    }

    private function reportQuery(): Builder
    {
        return FinishedGood::query()
            ->select(['id', 'code', 'description'])
            ->whereHas('rawMaterialFormula.items')
            ->with([
                'rawMaterialFormula.items' => fn ($query) => $query
                    ->select(['id', 'finished_good_formula_id', 'raw_material_id', 'standard', 'position']),
                'rawMaterialFormula.items.rawMaterial' => fn ($query) => $query
                    ->select(['id', 'code', 'material_id', 'description', 'unit', 'wastage_all']),
                'rawMaterialFormula.items.rawMaterial.prices' => fn ($query) => $query
                    ->select(['id', 'raw_material_id', 'period', 'rupiah_amount'])
                    ->whereIn('period', RawMaterialPrice::PERIODS),
            ])
            ->orderBy('code');
    }

    private function finishedGoods(array $filters): Builder
    {
        return $this->reportQuery()
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $query->where('code', 'like', "%{$filters['search']}%")
                        ->orWhere('description', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['codeFrom'] !== '', fn (Builder $query) => $query->where('code', '>=', $filters['codeFrom']))
            ->when($filters['codeTo'] !== '', fn (Builder $query) => $query->where('code', '<=', $filters['codeTo']));
    }

    /**
     * Per baris RM:
     *   C = A + (A * B)          (A = standard, B = wastage_all)
     *   Unit Cost = C * Harga     untuk Packaging (punya material_id / ID)
     *   Unit Cost = C * Harga / 1000 untuk RM (tanpa ID)
     * Total per FG = jumlah Unit Cost tanpa pembulatan per baris, sama seperti Report2() dan Calc3.
     */
    private function report(FinishedGood $finishedGood): array
    {
        $totals = array_fill_keys(RawMaterialPrice::PERIODS, 0.0);
        $rows = [];

        foreach ($finishedGood->rawMaterialFormula->items as $item) {
            $rawMaterial = $item->rawMaterial;
            $standard = (float) $item->standard;
            $waste = $rawMaterial ? (float) $rawMaterial->wastage_all : 0.0;
            $standardWithWaste = $standard + ($standard * $waste);
            $isPackaging = FinishedGoodFormulaItem::isPackaging($rawMaterial);
            $prices = $rawMaterial ? $rawMaterial->prices->keyBy('period') : collect();
            $periods = [];

            foreach (RawMaterialPrice::PERIODS as $period) {
                $price = (float) (optional($prices->get($period))->rupiah_amount ?? 0);
                $cost = $standardWithWaste * $price;
                $cost = $isPackaging ? $cost : $cost / 1000;

                $periods[$period] = ['price' => $price, 'cost' => $cost];
                $totals[$period] += $cost;
            }

            $rows[] = [
                'code' => optional($rawMaterial)->code,
                'description' => optional($rawMaterial)->description,
                'material_id' => optional($rawMaterial)->material_id,
                'unit' => optional($rawMaterial)->unit,
                'missing' => $rawMaterial === null,
                'is_packaging' => $isPackaging,
                'standard' => $standard,
                'waste' => $waste,
                'standard_with_waste' => $standardWithWaste,
                'periods' => $periods,
            ];
        }

        return [
            'code' => $finishedGood->code,
            'description' => $finishedGood->description,
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim($request->string('search')->toString()),
            'codeFrom' => trim($request->string('code_from')->toString()),
            'codeTo' => trim($request->string('code_to')->toString()),
        ];
    }

    private function references(): EloquentCollection
    {
        return Reference::query()
            ->orderBy('code')
            ->get(['id', 'code', 'description_1', 'description_2', 'period', 'period_description']);
    }

    private function selectedReference(Request $request, EloquentCollection $references): ?Reference
    {
        return $references->firstWhere('id', $request->integer('reference_id'))
            ?? $references->first();
    }
}
