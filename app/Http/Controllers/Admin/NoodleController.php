<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\NoodleRequest;
use App\Models\Noodle;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoodleController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.noodle.noodle', [
            'title' => __('Noodle'),
            ...$this->getNoodles($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->paginationPayload($this->getNoodles($request)['noodles']));
    }

    public function create(): View
    {
        return view('pages.user.maintenance.noodle.createNoodle', ['title' => __('Tambah Noodle')]);
    }

    public function store(NoodleRequest $request): RedirectResponse
    {
        Noodle::create([...$request->validated(), 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);

        return redirect()->route('admin.maintenance.noodle.index')->with('success', __('Noodle berhasil ditambahkan.'));
    }

    public function update(NoodleRequest $request, Noodle $noodle): JsonResponse
    {
        $noodle->update([...$request->validated(), 'updated_by' => $request->user()->id]);

        return response()->json(['message' => __('Noodle berhasil diperbarui.'), 'data' => $noodle->fresh()]);
    }

    public function destroy(Noodle $noodle): JsonResponse
    {
        $usages = [];

        if ($noodle->formula()->exists()) {
            $usages[] = __('Formula NDL');
        }

        if ($noodle->volumes()->exists()) {
            $usages[] = __('Volume Noodle');
        }

        if ($usages !== []) {
            return response()->json([
                'message' => __('Noodle :code tidak dapat dihapus karena masih digunakan pada: :usages. Hapus relasinya terlebih dahulu.', [
                    'code' => $noodle->code,
                    'usages' => implode(', ', $usages),
                ]),
            ], 422);
        }

        try {
            $noodle->delete();
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['19', '23000'], true)) {
                throw $exception;
            }

            return response()->json([
                'message' => __('Noodle tidak dapat dihapus karena masih digunakan oleh data lain.'),
            ], 422);
        }

        return response()->json(['message' => __('Noodle berhasil dihapus.')]);
    }

    private function getNoodles(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sort = in_array($request->string('sort')->toString(), ['code', 'description', 'unit'], true) ? $request->string('sort')->toString() : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true) ? $request->integer('per_page') : 5;

        $noodles = Noodle::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)
            ->when($sort !== 'code', fn ($query) => $query->orderBy('code'))
            ->paginate($perPage)
            ->withQueryString();

        return compact('noodles', 'search', 'sort', 'direction', 'perPage');
    }

    private function paginationPayload($paginator): array
    {
        return ['data' => $paginator->items(), 'meta' => ['current_page' => $paginator->currentPage(), 'last_page' => $paginator->lastPage(), 'from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $paginator->total(), 'per_page' => $paginator->perPage()]];
    }
}
