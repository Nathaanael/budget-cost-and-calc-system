<?php

namespace App\Http\Controllers\Admin\Reporting;

use App\Exports\RmPriceBudgetWorkbook;
use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\Reference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RmPriceBudgetController extends Controller
{
    public function index(Request $request): View
    {
        $references = $this->references();
        $selectedReference = $this->selectedReference($request, $references);
        $filters = $this->filters($request);
        $materials = $this->materials($filters)
            ->paginate(20)
            ->withQueryString();

        return view('pages.user.reporting.rm-price-budget', [
            'title' => __('RM Price Budget'),
            'references' => $references,
            'selectedReference' => $selectedReference,
            'materials' => $materials,
            'filters' => $filters,
        ]);
    }

    public function excel(Request $request, RmPriceBudgetWorkbook $workbook): Response
    {
        $references = $this->references();
        $selectedReference = $this->selectedReference($request, $references);
        $materials = $this->materials($this->filters($request))->get();
        $period = $selectedReference?->period_description ?: now()->format('Y-m-d');
        $filename = 'anggaran-harga-rm-'.str($period)->slug().'.xlsx';

        return response($workbook->make($selectedReference, $materials), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function materials(array $filters): Builder
    {
        return RawMaterial::query()
            ->select(['id', 'code', 'material_id', 'description', 'unit', 'currency_type', 'type_rm'])
            ->with(['prices' => fn ($query) => $query
                ->select(['id', 'raw_material_id', 'period', 'usd_amount', 'rupiah_amount', 'source_kind'])
                ->orderBy('period')])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $query->where('code', 'like', "%{$filters['search']}%")
                        ->orWhere('description', 'like', "%{$filters['search']}%")
                        ->orWhere('material_id', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['codeFrom'] !== '', fn (Builder $query) => $query->where('code', '>=', $filters['codeFrom']))
            ->when($filters['codeTo'] !== '', fn (Builder $query) => $query->where('code', '<=', $filters['codeTo']))
            ->when($filters['filledOnly'], fn (Builder $query) => $query->whereHas('prices', function (Builder $query): void {
                $query->where('usd_amount', '>', 0)->orWhere('rupiah_amount', '>', 0);
            }))
            ->orderBy('code');
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim($request->string('search')->toString()),
            'codeFrom' => trim($request->string('code_from')->toString()),
            'codeTo' => trim($request->string('code_to')->toString()),
            'filledOnly' => $request->boolean('filled_only'),
        ];
    }

    private function references(): EloquentCollection
    {
        return Reference::query()
            ->orderBy('code')
            ->get([
                'id', 'code', 'description_1', 'description_2', 'period', 'period_description',
                'rate_current', 'rate_le', 'rate_1', 'rate_2', 'rate_3', 'rate_4',
            ]);
    }

    private function selectedReference(Request $request, EloquentCollection $references): ?Reference
    {
        return $references->firstWhere('id', $request->integer('reference_id'))
            ?? $references->first();
    }
}
