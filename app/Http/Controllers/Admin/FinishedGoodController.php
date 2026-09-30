<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\FinishedGoodRequest;
use App\Models\FinishedGood;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinishedGoodController extends Controller
{
    private const PERIODS = ['current', 'le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'];

    public function index(Request $request): View
    {
        return view('pages.user.maintenance.finishedGood.finishedGood', ['title' => __('Finished Good'), ...$this->getFinishedGoods($request)]);
    }

    public function data(Request $request): JsonResponse
    {
        $items = $this->getFinishedGoods($request)['finishedGoods'];

        return response()->json(['data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'from' => $items->firstItem() ?? 0, 'to' => $items->lastItem() ?? 0, 'total' => $items->total(), 'per_page' => $items->perPage()]]);
    }

    public function create(): View
    {
        return view('pages.user.maintenance.finishedGood.createFinishedGood', ['title' => __('Tambah Finished Good')]);
    }

    public function store(FinishedGoodRequest $request): RedirectResponse
    {
        FinishedGood::create([...$this->normalizedPayload($request->validated()), 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);

        return redirect()->route('admin.maintenance.finished-good.index')->with('success', __('Finished good berhasil ditambahkan.'));
    }

    public function edit(FinishedGood $finishedGood): View
    {
        return view('pages.user.maintenance.finishedGood.editFinishedGood', [
            'title' => __('Edit Finished Good'),
            'finishedGood' => $finishedGood,
        ]);
    }

    public function update(FinishedGoodRequest $request, FinishedGood $finishedGood): JsonResponse|RedirectResponse
    {
        $finishedGood->update([...$this->normalizedPayload($request->validated()), 'updated_by' => $request->user()->id]);

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Finished good berhasil diperbarui.'), 'data' => $finishedGood->fresh()]);
        }

        return redirect()->route('admin.maintenance.finished-good.index')->with('success', __('Finished good berhasil diperbarui.'));
    }

    public function destroy(FinishedGood $finishedGood): JsonResponse
    {
        $finishedGood->delete();

        return response()->json(['message' => __('Finished good berhasil dihapus.')]);
    }

    private function getFinishedGoods(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sortableFields = ['code', 'description', 'description_1', 'product_type_1', 'product_type_2', 'batch', 'selling_price', 'multi_level', 'active', 'unit_cost_current', 'unit_price_current', 'unit_cost_le', 'unit_price_le', 'unit_cost_qtr_1', 'unit_price_qtr_1', 'unit_cost_qtr_2', 'unit_price_qtr_2', 'unit_cost_qtr_3', 'unit_price_qtr_3', 'unit_cost_qtr_4', 'unit_price_qtr_4'];
        $sort = in_array($request->string('sort')->toString(), $sortableFields, true) ? $request->string('sort')->toString() : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true) ? $request->integer('per_page') : 5;
        $finishedGoods = FinishedGood::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere('description_1', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return compact('finishedGoods', 'search', 'sort', 'direction', 'perPage');
    }

    private function normalizedPayload(array $validated): array
    {
        foreach (['batch', 'selling_price'] as $field) {
            $validated[$field] ??= 0;
        }

        foreach (self::PERIODS as $period) {
            $validated["unit_cost_{$period}"] ??= 0;
            $validated["unit_price_{$period}"] ??= 0;
        }

        return $validated;
    }
}
