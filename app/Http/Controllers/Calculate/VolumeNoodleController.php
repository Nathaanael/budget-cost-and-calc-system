<?php

namespace App\Http\Controllers\Calculate;

use App\Http\Controllers\Controller;
use App\Models\AreaNoodle;
use App\Models\NoodleFormula;
use App\Models\VolumeNoodle;
use App\Services\VolumeNoodleCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VolumeNoodleController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true) ? $request->integer('per_page') : 5;

        return view('pages.user.calculate.volumeNoodle.volumeNoodle', [
            'title' => __('Calculate Volume Noodle'),
            'period' => $request->query('period') === 'le' ? 'le' : 'aop',
            'perPage' => $perPage,
            'inputs' => $this->inputRows(new Request),
            'areas' => AreaNoodle::orderBy('code')->get(['id', 'code', 'description']),
            'results' => DB::table('finished_good_volumes')->orderBy('area_code')->orderBy('fg_code')->orderBy('id')->paginate($perPage)->withQueryString(),
            'calculation' => DB::table('volume_calculation_state')->where('id', 1)->first(),
            'sourceCounts' => [
                'Areas' => AreaNoodle::count(),
                'Volume input records' => VolumeNoodle::count(),
                'Noodle formulas with items' => NoodleFormula::whereHas('items')->count(),
            ],
        ]);
    }

    public function inputs(Request $request): JsonResponse
    {
        return response()->json($this->inputRows($request));
    }

    private function inputRows(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $rows = VolumeNoodle::query()
            ->where('area_noodle_id', $request->integer('area_id'))
            ->with(['areaNoodle:id,code,description', 'noodle:id,code,description'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('areaNoodle', fn ($area) => $area->where('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"))
                        ->orWhereHas('noodle', fn ($noodle) => $noodle->where('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
                });
            })
            ->orderBy('area_noodle_id')->orderBy('noodle_id')
            ->paginate(5, ['*'], 'input_page');

        return [
            'data' => $rows->items(),
            'current_page' => $rows->currentPage(), 'last_page' => $rows->lastPage(),
            'from' => $rows->firstItem() ?? 0, 'to' => $rows->lastItem() ?? 0, 'total' => $rows->total(),
        ];
    }

    public function store(Request $request, VolumeNoodleCalculationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'confirm' => ['required', 'accepted'],
            'area_id' => ['required', 'integer', 'exists:area_noodles,id'],
        ]);
        $count = $service->calculate($request->user()->id, (int) $validated['area_id']);

        return redirect()->route('admin.calculate.volume-noodle.index')->with(
            'success', __('FG volumes calculated and saved: :count records.', ['count' => $count])
        );
    }
}
