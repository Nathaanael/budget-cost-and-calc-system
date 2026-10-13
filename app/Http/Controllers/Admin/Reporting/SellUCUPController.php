<?php

namespace App\Http\Controllers\Admin\Reporting;

use App\Exports\SellUCUPWorkbook;
use App\Http\Controllers\Concerns\BatchesExcelExport;
use App\Http\Controllers\Controller;
use App\Models\FinishedGood;
use App\Models\Reference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Port Report3() di REP1.PRG: "SELLING PRICE & UNIT COST".
 * Report ini hanya menampilkan nilai tersimpan hasil Calculate U.Cost+U.Price (Calc3), tanpa kalkulasi ulang.
 */
class SellUCUPController extends Controller
{
    use BatchesExcelExport;

    public const PERIODS = [
        'current' => 'Current',
        'le' => 'LE',
        'qtr_1' => 'Kuartal 1',
        'qtr_2' => 'Kuartal 2',
        'qtr_3' => 'Kuartal 3',
        'qtr_4' => 'Kuartal 4',
    ];

    // Urutan blok sama dengan Report3(): Selling Price per pabrik, lalu Unit Cost.
    public const BLOCKS = [
        'cikampek' => ['label' => 'Selling Price Cikampek', 'prefix' => 'unit_price'],
        'semarang' => ['label' => 'Selling Price Semarang', 'prefix' => 'unit_price_semarang'],
        'surabaya' => ['label' => 'Selling Price Surabaya', 'prefix' => 'unit_price_surabaya'],
        'palembang' => ['label' => 'Selling Price Palembang', 'prefix' => 'unit_price_palembang'],
        'unit_cost' => ['label' => 'Unit Cost', 'prefix' => 'unit_cost'],
    ];

    private const IDENTITY_FIELDS = ['id', 'code', 'description', 'product_type_1', 'product_type_2'];

    public function index(Request $request): View
    {
        $references = $this->references();
        $filters = $this->filters($request);

        return view('pages.user.reporting.sell-uc-up', [
            'title' => __('Unit Cost & Sell.Price'),
            'references' => $references,
            'selectedReference' => $this->selectedReference($request, $references),
            'finishedGoods' => $this->finishedGoods($filters)->paginate(25)->withQueryString(),
            'filters' => $filters,
            'productTypes1' => $this->productTypeOptions('product_type_1'),
            'productTypes2' => $this->productTypeOptions('product_type_2'),
            'blocks' => self::BLOCKS,
            'periods' => self::PERIODS,
        ]);
    }

    /**
     * Tahap akhir unduh bertahap (lihat BatchesExcelExport): susun file Excel dari seluruh batch.
     */
    public function excel(Request $request, SellUCUPWorkbook $workbook): Response
    {
        $finishedGoods = $this->exportBatches($request);
        $references = $this->references();
        $selectedReference = $this->selectedReference($request, $references);
        $period = optional($selectedReference)->period_description ?: now()->format('Y-m-d');
        $filename = 'selling-price-unit-cost-'.Str::slug($period).'.xlsx';

        return response($workbook->make($selectedReference, $finishedGoods, $this->filters($request)), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    protected function exportFinishedGoodIds(Request $request): Collection
    {
        return $this->finishedGoods($this->filters($request))->pluck('id');
    }

    /**
     * Report ini hanya membaca nilai tersimpan, jadi batch boleh lebih besar dari report yang menghitung ulang.
     */
    protected function exportBatchSize(): int
    {
        return 200;
    }

    protected function exportBatchRows(array $finishedGoodIds): array
    {
        return $this->reportQuery()
            ->whereIn('id', $finishedGoodIds)
            ->get()
            ->map(fn (FinishedGood $finishedGood) => $finishedGood->only(array_merge(self::IDENTITY_FIELDS, self::valueFields())))
            ->all();
    }

    /**
     * Tahun AOP = tahun periode referensi + 1 (Report3: val(right(CostRef->Per, 4)) + 1).
     */
    public static function aopYear(?Reference $reference): ?int
    {
        $year = $reference ? substr(trim((string) $reference->period), -4) : '';

        return ctype_digit($year) ? (int) $year + 1 : null;
    }

    public static function valueFields(): array
    {
        $fields = [];

        foreach (self::BLOCKS as $block) {
            foreach (array_keys(self::PERIODS) as $period) {
                $fields[] = "{$block['prefix']}_{$period}";
            }
        }

        return $fields;
    }

    /**
     * Filter Report3(): FG aktif, dalam rentang kode, dan minimal satu Unit Cost / Selling Price tidak nol.
     * Report3() lama lupa mengecek Selling Price Palembang saat mencetak baris; di sini semua blok diperiksa.
     */
    private function finishedGoods(array $filters): Builder
    {
        $valueFields = self::valueFields();

        return $this->reportQuery()
            ->where('active', 'Y')
            ->where(function (Builder $query) use ($valueFields): void {
                foreach ($valueFields as $field) {
                    $query->orWhere($field, '!=', 0);
                }
            })
            ->when($filters['productType1'] !== '', fn (Builder $query) => $query->where('product_type_1', (int) $filters['productType1']))
            ->when($filters['productType2'] !== '', fn (Builder $query) => $query->where('product_type_2', (int) $filters['productType2']))
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $query->where('code', 'like', "%{$filters['search']}%")
                        ->orWhere('description', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['codeFrom'] !== '', fn (Builder $query) => $query->where('code', '>=', $filters['codeFrom']))
            ->when($filters['codeTo'] !== '', fn (Builder $query) => $query->where('code', '<=', $filters['codeTo']));
    }

    private function reportQuery(): Builder
    {
        return FinishedGood::query()
            ->select(array_merge(self::IDENTITY_FIELDS, self::valueFields()))
            // Index FGMast3 lama: PrType1 + PrType2 + FGCode.
            ->orderBy('product_type_1')
            ->orderBy('product_type_2')
            ->orderBy('code');
    }

    private function productTypeOptions(string $field): Collection
    {
        return FinishedGood::query()
            ->where('active', 'Y')
            ->distinct()
            ->orderBy($field)
            ->pluck($field);
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim($request->string('search')->toString()),
            'codeFrom' => trim($request->string('code_from')->toString()),
            'codeTo' => trim($request->string('code_to')->toString()),
            'productType1' => $this->productTypeFilter($request, 'product_type_1'),
            'productType2' => $this->productTypeFilter($request, 'product_type_2'),
        ];
    }

    private function productTypeFilter(Request $request, string $key): string
    {
        $value = trim($request->string($key)->toString());

        return ctype_digit($value) ? (string) (int) $value : '';
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
