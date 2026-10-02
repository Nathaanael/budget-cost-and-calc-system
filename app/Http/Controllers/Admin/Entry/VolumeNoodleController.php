<?php

namespace App\Http\Controllers\Admin\Entry;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entry\VolumeNoodleRequest;
use App\Models\AreaNoodle;
use App\Models\Noodle;
use App\Models\VolumeNoodle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolumeNoodleController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.entry.volumeNoodle.volumeNoodle', [
            'title' => __('Volume Noodle'),
            ...$this->getVolumes($request),
            ...$this->getOptions(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $volumes = $this->getVolumes($request)['volumes'];

        return response()->json([
            'data' => $volumes->items(),
            'meta' => [
                'current_page' => $volumes->currentPage(),
                'last_page' => $volumes->lastPage(),
                'from' => $volumes->firstItem() ?? 0,
                'to' => $volumes->lastItem() ?? 0,
                'total' => $volumes->total(),
                'per_page' => $volumes->perPage(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.user.entry.volumeNoodle.createVolumeNdl', [
            'title' => __('Tambah Volume Noodle'),
            ...$this->getOptions(),
        ]);
    }

    public function store(VolumeNoodleRequest $request): RedirectResponse
    {
        VolumeNoodle::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.entry.volume-noodle.index')
            ->with('success', __('Volume noodle berhasil ditambahkan.'));
    }

    public function update(VolumeNoodleRequest $request, VolumeNoodle $volumeNoodle): JsonResponse
    {
        $volumeNoodle->update([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => __('Volume noodle berhasil diperbarui.'),
            'data' => $this->serialize($volumeNoodle->fresh(['areaNoodle', 'noodle'])),
        ]);
    }

    public function destroy(VolumeNoodle $volumeNoodle): JsonResponse
    {
        $volumeNoodle->delete();

        return response()->json(['message' => __('Volume noodle berhasil dihapus.')]);
    }

    private function getVolumes(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sortableFields = ['area_code', 'noodle_code', ...VolumeNoodle::LE_FIELDS, 'total_le', ...VolumeNoodle::MONTH_FIELDS, 'total_aop'];
        $sort = in_array($request->string('sort')->toString(), $sortableFields, true)
            ? $request->string('sort')->toString()
            : 'area_code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;
        $sortColumn = match ($sort) {
            'area_code' => 'area_noodles.code',
            'noodle_code' => 'noodles.code',
            default => "volume_noodles.{$sort}",
        };

        $volumes = VolumeNoodle::query()
            ->with(['areaNoodle:id,code,description', 'noodle:id,code,description'])
            ->join('area_noodles', 'area_noodles.id', '=', 'volume_noodles.area_noodle_id')
            ->join('noodles', 'noodles.id', '=', 'volume_noodles.noodle_id')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('area_noodles.code', 'like', "%{$search}%")
                        ->orWhere('area_noodles.description', 'like', "%{$search}%")
                        ->orWhere('noodles.code', 'like', "%{$search}%")
                        ->orWhere('noodles.description', 'like', "%{$search}%");
                });
            })
            ->select('volume_noodles.*')
            ->orderBy($sortColumn, $direction)
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (VolumeNoodle $volume) => $this->serialize($volume));

        return compact('volumes', 'search', 'sort', 'direction', 'perPage');
    }

    private function getOptions(): array
    {
        return [
            'areaOptions' => AreaNoodle::query()->orderBy('code')->get(['id', 'code', 'description']),
            'noodleOptions' => Noodle::query()->orderBy('code')->get(['id', 'code', 'description']),
        ];
    }

    private function serialize(VolumeNoodle $volume): array
    {
        return [
            'id' => $volume->id,
            'area_noodle_id' => $volume->area_noodle_id,
            'area_code' => $volume->areaNoodle->code,
            'area_description' => $volume->areaNoodle->description,
            'noodle_id' => $volume->noodle_id,
            'noodle_code' => $volume->noodle->code,
            'noodle_description' => $volume->noodle->description,
            ...$volume->only([...VolumeNoodle::LE_FIELDS, 'total_le', ...VolumeNoodle::MONTH_FIELDS, 'total_aop']),
        ];
    }
}
