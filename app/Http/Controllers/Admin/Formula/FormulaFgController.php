<?php

namespace App\Http\Controllers\Admin\Formula;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\SaveFinishedGoodFormulaRequest;
use App\Models\FinishedGood;
use App\Models\FinishedGoodFormula;
use App\Models\FinishedGoodFormulaItem;
use App\Models\RawMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
                ->get(['id', 'code', 'description'])
                ->toArray(),
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::exists(FinishedGood::class, 'code')],
        ]);

        $finishedGood = FinishedGood::where('code', $validated['code'])->firstOrFail();
        $formula = FinishedGoodFormula::query()
            ->where('finished_good_id', $finishedGood->id)
            ->with(['items' => fn ($query) => $query->withTrashed()->with('rawMaterial')])
            ->first();

        return response()->json(['data' => $this->formulaPayload($finishedGood, $formula)]);
    }

    public function update(SaveFinishedGoodFormulaRequest $request, FinishedGood $finishedGood): JsonResponse
    {
        $formula = DB::transaction(function () use ($request, $finishedGood) {
            $userId = $request->user()->id;
            $formula = FinishedGoodFormula::withTrashed()->firstOrNew([
                'finished_good_id' => $finishedGood->id,
            ]);

            if (! $formula->exists) {
                $formula->created_by = $userId;
            }

            $formula->updated_by = $userId;
            $formula->save();

            if ($formula->trashed()) {
                $formula->restore();
            }

            $rows = collect($request->validated('rows'));
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

                $item->fill([
                    'raw_material_id' => $rawMaterials->get($row['code'])->id,
                    'standard' => $row['standard'],
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
            'master' => $finishedGood->only(['id', 'code', 'description']),
            'finished_good' => $finishedGood->only(['id', 'code', 'description']),
            'formula_id' => $formula?->id,
            'items' => $formula?->items->map(fn (FinishedGoodFormulaItem $item) => [
                'id' => $item->id,
                'code' => $item->rawMaterial->code,
                'description' => $item->rawMaterial->description,
                'standard' => $item->standard,
                'deleted' => $item->trashed(),
            ])->values()->all() ?? [],
        ];
    }
}
