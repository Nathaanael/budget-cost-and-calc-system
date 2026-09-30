<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\AreaNoodleRequest;
use App\Models\AreaNoodle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreaNoodleController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.areaNoodle.areaNoodle', ['title' => __('Area Noodle'), ...$this->getAreas($request)]);
    }

    public function data(Request $request): JsonResponse
    {
        $areas = $this->getAreas($request)['areas'];

        return response()->json(['data' => $areas->items(), 'meta' => ['current_page' => $areas->currentPage(), 'last_page' => $areas->lastPage(), 'from' => $areas->firstItem() ?? 0, 'to' => $areas->lastItem() ?? 0, 'total' => $areas->total(), 'per_page' => $areas->perPage()]]);
    }

    public function create(): View
    {
        return view('pages.user.maintenance.areaNoodle.createAreaNoodle', ['title' => __('Tambah Area Noodle')]);
    }

    public function store(AreaNoodleRequest $request): RedirectResponse
    {
        AreaNoodle::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.maintenance.area-noodle.index')->with('success', __('Area noodle berhasil ditambahkan.'));
    }

    public function update(AreaNoodleRequest $request, AreaNoodle $areaNoodle): JsonResponse
    {
        $areaNoodle->update([...$request->validated(), 'updated_by' => $request->user()->id]);

        return response()->json(['message' => __('Area noodle berhasil diperbarui.'), 'data' => $areaNoodle->fresh()]);
    }

    public function destroy(AreaNoodle $areaNoodle): JsonResponse
    {
        $areaNoodle->delete();

        return response()->json(['message' => __('Area noodle berhasil dihapus.')]);
    }

    private function getAreas(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sort = in_array($request->string('sort')->toString(), ['code', 'description'], true) ? $request->string('sort')->toString() : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true) ? $request->integer('per_page') : 5;
        $areas = AreaNoodle::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return compact('areas', 'search', 'sort', 'direction', 'perPage');
    }
}
