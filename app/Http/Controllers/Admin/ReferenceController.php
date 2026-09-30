<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ReferenceRequest;
use App\Models\Reference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.user.maintenance.reference.reference', [
            'title' => __('Reference'),
            ...$this->getReferences($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->paginationPayload($this->getReferences($request)['references']));
    }

    public function create(): View
    {
        return view('pages.user.maintenance.reference.createReference', [
            'title' => __('Tambah Reference'),
        ]);
    }

    public function store(ReferenceRequest $request): RedirectResponse
    {
        Reference::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.maintenance.reference.index')
            ->with('success', __('Reference berhasil ditambahkan.'));
    }

    public function edit(Reference $reference): View
    {
        return view('pages.user.maintenance.reference.editReference', [
            'title' => __('Edit Reference'),
            'reference' => $reference,
        ]);
    }

    public function update(ReferenceRequest $request, Reference $reference): JsonResponse|RedirectResponse
    {
        $reference->update([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Reference berhasil diperbarui.'),
                'data' => $reference->fresh(),
            ]);
        }

        return redirect()
            ->route('admin.maintenance.reference.index')
            ->with('success', __('Reference berhasil diperbarui.'));
    }

    public function destroy(Reference $reference): JsonResponse
    {
        $reference->delete();

        return response()->json(['message' => __('Reference berhasil dihapus.')]);
    }

    private function getReferences(Request $request): array
    {
        $search = trim($request->string('search')->toString());
        $sortableFields = ['code', 'description_1', 'description_2', 'period', 'period_description', 'rate_current'];
        $sort = in_array($request->string('sort')->toString(), $sortableFields, true)
            ? $request->string('sort')->toString()
            : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->integer('per_page'), [5, 10, 20], true)
            ? $request->integer('per_page')
            : 5;

        $references = Reference::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('description_1', 'like', "%{$search}%")
                        ->orWhere('description_2', 'like', "%{$search}%")
                        ->orWhere('period', 'like', "%{$search}%")
                        ->orWhere('period_description', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->when($sort !== 'code', fn ($query) => $query->orderBy('code'))
            ->paginate($perPage)
            ->withQueryString();

        return compact('references', 'search', 'sort', 'direction', 'perPage');
    }

    private function paginationPayload($paginator): array
    {
        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
        ];
    }
}
