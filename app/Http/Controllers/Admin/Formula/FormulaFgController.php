<?php

namespace App\Http\Controllers\Admin\Formula;

use App\Exports\FinishedGoodFormulaWorkbook;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\SaveFinishedGoodFormulaRequest;
use App\Models\FinishedGood;
use App\Models\FinishedGoodFormula;
use App\Models\FinishedGoodFormulaItem;
use App\Models\RawMaterial;
use App\Support\FinishedGoodFormulaSpreadsheet;
use App\Support\PlantContext;
use App\Support\SpreadsheetReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class FormulaFgController extends Controller
{
    public function index(): View
    {
        return view('pages.user.maintenance.formula.formulaFg', [
            'title' => __('Formula FG'),
            'masterItems' => FinishedGood::query()
                ->orderBy('code')
                ->get(['id', 'code', 'description'])
                ->toArray(),
            'ingredientItems' => RawMaterial::query()
                ->orderBy('code')
                ->get(['id', 'code', 'description', 'material_id'])
                ->map(fn (RawMaterial $rawMaterial) => [
                    'id' => $rawMaterial->id,
                    'code' => $rawMaterial->code,
                    'description' => $rawMaterial->description,
                    'is_packaging' => FinishedGoodFormulaItem::isPackaging($rawMaterial),
                ])
                ->all(),
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::exists(FinishedGood::class, 'code')->where('plant_id', app(PlantContext::class)->id())],
        ]);

        $finishedGood = FinishedGood::where('code', $validated['code'])->firstOrFail();
        $formula = FinishedGoodFormula::query()
            ->where('finished_good_id', $finishedGood->id)
            ->with(['items' => fn ($query) => $query->withTrashed()->with('rawMaterial')])
            ->first();

        return response()->json(['data' => $this->formulaPayload($finishedGood, $formula)]);
    }

    /**
     * Download Formula FG (data tersimpan) dengan layout template Excel.
     * ?code=XXX untuk satu FG yang ditampilkan, tanpa code untuk semua formula FG di plant aktif.
     */
    public function export(Request $request, FinishedGoodFormulaWorkbook $workbook): Response
    {
        $plantId = app(PlantContext::class)->id();
        $validated = $request->validate([
            'code' => ['nullable', 'string', Rule::exists(FinishedGood::class, 'code')->where('plant_id', $plantId)],
        ]);
        $code = $validated['code'] ?? null;

        $finishedGoods = FinishedGood::query()
            ->when($code, fn ($query) => $query->where('code', $code), fn ($query) => $query->whereHas('rawMaterialFormula.items'))
            ->with('rawMaterialFormula')
            ->orderBy('code')
            ->get(['id', 'code', 'description', 'batch']);

        $items = DB::table('finished_good_formula_items as items')
            ->join('finished_good_formulas as formulas', 'formulas.id', '=', 'items.finished_good_formula_id')
            ->join('raw_materials', 'raw_materials.id', '=', 'items.raw_material_id')
            ->whereIn('formulas.finished_good_id', $finishedGoods->pluck('id'))
            ->whereNull('formulas.deleted_at')
            ->whereNull('items.deleted_at')
            ->orderBy('items.position')
            ->get([
                'formulas.finished_good_id',
                'raw_materials.code',
                'raw_materials.description',
                'raw_materials.unit',
                'raw_materials.material_id',
                'items.standard_batch',
                'items.standard',
            ])
            ->groupBy('finished_good_id');

        $formulas = $finishedGoods->map(fn (FinishedGood $finishedGood) => [
            'code' => $finishedGood->code,
            'description' => $finishedGood->description,
            'batch' => (int) $finishedGood->batch,
            'notes' => optional($finishedGood->rawMaterialFormula)->notes,
            'items' => $items->get($finishedGood->id, collect()),
        ]);

        $filename = $code
            ? 'formula-fg-'.Str::slug($code).'.xlsx'
            : 'formula-fg-semua-'.now()->format('Ymd').'.xlsx';

        return response($workbook->make($formulas), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * Membaca formula dari template Excel sebagai pratinjau. Data belum disimpan;
     * user meninjau/mengedit baris lalu menyimpan lewat tombol Simpan Formula.
     */
    public function import(Request $request, FinishedGood $finishedGood, SpreadsheetReader $reader, FinishedGoodFormulaSpreadsheet $template): JsonResponse
    {
        abort_unless(optional($request->user())->isSuperadmin() === true, 403);

        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['xlsx', 'ods'], true)) {
            throw ValidationException::withMessages([
                'file' => __('Format file harus .xlsx atau .ods.'),
            ]);
        }

        try {
            $sheet = $template->parse($reader->rows($file->getRealPath(), $extension, 'FORMULA'));
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['file' => $exception->getMessage()]);
        }

        if ($sheet['fg_code'] !== strtoupper($finishedGood->code)) {
            throw ValidationException::withMessages([
                'file' => __('Kode FG pada Excel (:excel) tidak sesuai dengan FG terpilih (:system). Cek kembali file Excel Anda.', [
                    'excel' => $sheet['fg_code'],
                    'system' => $finishedGood->code,
                ]),
            ]);
        }

        $duplicates = collect($sheet['items'])->groupBy('code')->filter(fn ($rows) => $rows->count() > 1);

        if ($duplicates->isNotEmpty()) {
            throw ValidationException::withMessages([
                'file' => __('Code RM berikut muncul lebih dari satu kali di Excel: :codes. Gabungkan barisnya lalu upload ulang.', [
                    'codes' => $duplicates->keys()->implode(', '),
                ]),
            ]);
        }

        $rawMaterials = RawMaterial::query()
            ->whereIn('code', collect($sheet['items'])->pluck('code'))
            ->get(['id', 'code', 'description', 'material_id'])
            ->keyBy(fn (RawMaterial $rawMaterial) => strtoupper($rawMaterial->code));
        $missing = collect($sheet['items'])
            ->reject(fn (array $item) => $rawMaterials->has($item['code']))
            ->map(fn (array $item) => ['row' => $item['row'], 'no' => $item['no'], 'code' => $item['code'], 'name' => $item['name']])
            ->values();

        if ($missing->isNotEmpty()) {
            return response()->json([
                'message' => __(':count raw material/packaging belum terdaftar di Maintenance Raw Material. Tambahkan terlebih dahulu, lalu upload ulang file Excel.', ['count' => $missing->count()]),
                'missing_raw_materials' => $missing->all(),
            ], 422);
        }

        $batch = (int) $finishedGood->batch;
        $warnings = [];

        if ($sheet['output'] !== null && (int) round($sheet['output']) !== $batch) {
            $warnings[] = __('OUTPUT di Excel (:excel) berbeda dengan Batch FG di sistem (:system). Standard Unit dihitung memakai Batch sistem.', [
                'excel' => number_format($sheet['output'], 0, ',', '.'),
                'system' => number_format($batch, 0, ',', '.'),
            ]);
        }

        if ($sheet['skipped'] !== []) {
            $warnings[] = __('Baris tanpa NO dilewati sebagai sub-header premix: :rows.', [
                'rows' => collect($sheet['skipped'])->map(fn (array $row) => "{$row['code']} {$row['name']} (baris {$row['row']})")->implode('; '),
            ]);
        }

        return response()->json([
            'message' => __(':count baris berhasil dibaca dari Excel. Periksa data lalu klik Simpan Formula.', ['count' => count($sheet['items'])]),
            'warnings' => $warnings,
            'data' => [
                'notes' => $sheet['notes'],
                'items' => collect($sheet['items'])->map(function (array $item) use ($rawMaterials, $batch) {
                    $rawMaterial = $rawMaterials->get($item['code']);
                    $isPackaging = FinishedGoodFormulaItem::isPackaging($rawMaterial);

                    return [
                        'id' => null,
                        'code' => $rawMaterial->code,
                        'description' => $rawMaterial->description,
                        'standard_batch' => $item['standard_batch'],
                        'standard' => FinishedGoodFormulaItem::calculateStandardUnit($item['standard_batch'], $batch, $isPackaging),
                        'is_packaging' => $isPackaging,
                        'deleted' => false,
                    ];
                })->values()->all(),
            ],
        ]);
    }

    public function update(SaveFinishedGoodFormulaRequest $request, FinishedGood $finishedGood): JsonResponse
    {
        $batch = (int) $finishedGood->batch;

        if ($batch <= 0) {
            throw ValidationException::withMessages([
                'rows' => __('Batch Finished Good belum diisi. Isi Batch pada Maintenance Finished Good sebelum menyimpan formula.'),
            ]);
        }

        $formula = DB::transaction(function () use ($request, $finishedGood, $batch) {
            $userId = $request->user()->id;
            $formula = FinishedGoodFormula::withTrashed()->firstOrNew([
                'finished_good_id' => $finishedGood->id,
            ]);

            if (! $formula->exists) {
                $formula->created_by = $userId;
            }

            $formula->updated_by = $userId;

            if ($request->has('notes')) {
                $notes = trim((string) $request->validated()['notes']);
                $formula->notes = $notes === '' ? null : $notes;
            }
            $formula->save();

            if ($formula->trashed()) {
                $formula->restore();
            }

            $rows = collect(data_get($request->validated(), 'rows'));
            $submittedIds = $rows->pluck('id')->filter()->map(fn ($id) => (int) $id);
            $existingItems = $formula->items()->withTrashed()->get()->keyBy('id');
            $invalidId = $submittedIds->first(fn ($id) => ! $existingItems->has($id));

            if ($invalidId !== null) {
                throw ValidationException::withMessages([
                    'rows' => __('Terdapat baris formula yang bukan milik finished good ini.'),
                ]);
            }

            $formula->items()
                ->withTrashed()
                ->whereNotIn('id', $submittedIds->all())
                ->get()
                ->each->forceDelete();

            $rawMaterials = RawMaterial::query()
                ->whereIn('code', $rows->pluck('code'))
                ->get()
                ->keyBy('code');

            foreach ($rows as $position => $row) {
                $item = isset($row['id'])
                    ? $existingItems->get((int) $row['id'])
                    : new FinishedGoodFormulaItem([
                        'finished_good_formula_id' => $formula->id,
                        'created_by' => $userId,
                    ]);

                $rawMaterial = $rawMaterials->get($row['code']);

                $item->fill([
                    'raw_material_id' => $rawMaterial->id,
                    'standard_batch' => $row['standard_batch'],
                    'standard' => FinishedGoodFormulaItem::calculateStandardUnit(
                        $row['standard_batch'],
                        $batch,
                        FinishedGoodFormulaItem::isPackaging($rawMaterial),
                    ),
                    'position' => $position + 1,
                    'updated_by' => $userId,
                ]);
                $item->save();

                if ((bool) ($row['deleted'] ?? false)) {
                    $item->delete();
                } elseif ($item->trashed()) {
                    $item->restore();
                }
            }

            return $formula->fresh([
                'items' => fn ($query) => $query->withTrashed()->with('rawMaterial'),
            ]);
        });

        return response()->json([
            'message' => __('Formula FG berhasil disimpan.'),
            'data' => $this->formulaPayload($finishedGood, $formula),
        ]);
    }

    private function formulaPayload(FinishedGood $finishedGood, ?FinishedGoodFormula $formula): array
    {
        return [
            'master' => $finishedGood->only(['id', 'code', 'description', 'batch']),
            'finished_good' => $finishedGood->only(['id', 'code', 'description', 'batch']),
            'formula_id' => optional($formula)->id,
            'notes' => optional($formula)->notes ?? '',
            'items' => $formula ? $formula->items->map(fn (FinishedGoodFormulaItem $item) => [
                'id' => $item->id,
                'code' => $item->rawMaterial->code,
                'description' => $item->rawMaterial->description,
                'standard_batch' => $item->standard_batch,
                'standard' => $item->standard,
                'is_packaging' => FinishedGoodFormulaItem::isPackaging($item->rawMaterial),
                'deleted' => $item->trashed(),
            ])->values()->all() : [],
        ];
    }
}
