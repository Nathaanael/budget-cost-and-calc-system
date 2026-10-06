<?php

namespace App\Http\Controllers\Calculate;

use App\Http\Controllers\Controller;
use App\Models\Synonim;
use App\Services\MatchingPriceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MatchingController extends Controller
{
    public function index(): View
    {
        $materials = Synonim::with(['rawMaterial.prices', 'finishedGood'])->orderBy('raw_material_id')->get()->map(function ($mapping) {
            $prices = $mapping->rawMaterial->prices->keyBy('period');
            $sources = [];
            foreach (MatchingPriceService::FACTORIES as $factory => $prefix) {
                foreach (MatchingPriceService::PERIODS as $period) {
                    $sources[$factory][$period] = $mapping->finishedGood?->{$prefix.'_'.$period};
                }
            }

            return [
                'id' => $mapping->raw_material_id,
                'rm' => $mapping->rawMaterial->code, 'name' => $mapping->rawMaterial->description,
                'fg' => $mapping->finishedGood?->code, 'fg_name' => $mapping->finishedGood?->description,
                'prices' => $prices->map(fn ($price) => $price->rupiah_amount), 'sources' => $sources,
            ];
        });

        return view('pages.user.calculate.matchingPrice.matching', [
            'title' => __('Matching Price'), 'materials' => $materials,
            'history' => DB::table('matching_price_histories')->orderByDesc('id')->paginate(5, ['*'], 'history_page'),
        ]);
    }

    public function store(Request $request, MatchingPriceService $service): RedirectResponse
    {
        $validated = $request->validate([
            'factory' => ['required', Rule::in(array_keys(MatchingPriceService::FACTORIES))],
            'material_ids' => ['required', 'array', 'min:1'],
            'material_ids.*' => ['required', 'integer', 'distinct', Rule::exists('synonims', 'raw_material_id')],
        ]);
        $count = $service->match($validated['factory'], $request->user(), $validated['material_ids']);

        return redirect()->route('admin.calculate.matching-price.index')
            ->with('matching_factory', $validated['factory'])
            ->with('matching_success', __('Matching completed for :count materials across LE and Quarter 1–4.', ['count' => $count]));
    }
}
