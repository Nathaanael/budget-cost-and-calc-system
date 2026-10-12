<?php

namespace App\Http\Controllers\Admin\Formula;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\SaveNoodleFormulaRequest;
use App\Models\FinishedGood;
use App\Models\Noodle;
use App\Models\NoodleFormula;
use App\Models\NoodleFormulaItem;
use App\Support\PlantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FormulaNdlController extends Controller
{
    public function index(): View
    {
        return view('pages.user.maintenance.formula.formulaNdl', [
            'title' => __('Formula NDL'),
            'masterItems' => Noodle::query()
                ->orderBy('code')
                ->get(['id', 'code', 'description'])
                ->toArray(),
            'ingredientItems' => FinishedGood::query()
                ->where('active', 'Y')
                ->orderBy('code')
                ->get(['id', 'code', 'description'])
                ->toArray(),
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::exists(Noodle::class, 'code')->where('plant_id', app(PlantContext::class)->id())],
        ]);

        $noodle = Noodle::where('code', $validated['code'])->firstOrFail();
        $formula = NoodleFormula::query()
            ->where('noodle_id', $noodle->id)
            ->with(['items' => fn ($query) => $query->withTrashed()->with('finishedGood')])
            ->first();

        return response()->json(['data' => $this->formulaPayload($noodle, $formula)]);
    }

    public function update(SaveNoodleFormulaRequest $request, Noodle $noodle): JsonResponse
    {
        $formula = DB::transaction(function () use ($request, $noodle) {
            $userId = $request->user()->id;
            $formula = NoodleFormula::withTrashed()->firstOrNew(['noodle_id' => $noodle->id]);

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
                    'rows' => __('Terdapat baris formula yang bukan milik noodle ini.'),
                ]);
            }

            $formula->items()
                ->withTrashed()
                ->whereNotIn('id', $submittedIds->all())
                ->get()
                ->each->forceDelete();

            $finishedGoods = FinishedGood::query()
                ->whereIn('code', $rows->pluck('code'))
                ->get()
                ->keyBy('code');

            foreach ($rows as $position => $row) {
                $item = isset($row['id'])
                    ? $existingItems->get((int) $row['id'])
                    : new NoodleFormulaItem([
                        'noodle_formula_id' => $formula->id,
                        'created_by' => $userId,
                    ]);

                $item->fill([
                    'finished_good_id' => $finishedGoods->get($row['code'])->id,
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
                'items' => fn ($query) => $query->withTrashed()->with('finishedGood'),
            ]);
        });

        return response()->json([
            'message' => __('Formula NDL berhasil disimpan.'),
            'data' => $this->formulaPayload($noodle, $formula),
        ]);
    }

    private function formulaPayload(Noodle $noodle, ?NoodleFormula $formula): array
    {
        return [
            'master' => $noodle->only(['id', 'code', 'description']),
            'noodle' => $noodle->only(['id', 'code', 'description']),
            'formula_id' => $formula?->id,
            'items' => $formula?->items->map(fn (NoodleFormulaItem $item) => [
                'id' => $item->id,
                'code' => $item->finishedGood->code,
                'description' => $item->finishedGood->description,
                'standard' => $item->standard,
                'deleted' => $item->trashed(),
            ])->values()->all() ?? [],
        ];
    }
}
