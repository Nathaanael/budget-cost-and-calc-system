<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Unduh Excel bertahap (plan → batch → excel) agar halaman bisa menampilkan progress bar
 * dan tidak terlihat macet saat rentang FG besar. Dipasangkan dengan <x-common.batched-excel-download>.
 *
 * Controller pemakai menyediakan exportFinishedGoodIds() dan exportBatchRows().
 */
trait BatchesExcelExport
{
    /**
     * Tahap 1: daftar FG sesuai filter (sudah terurut) dan token untuk menampung hasil batch.
     */
    public function excelPlan(Request $request): JsonResponse
    {
        return response()->json([
            'token' => Str::random(32),
            'finished_good_ids' => $this->exportFinishedGoodIds($request)->values(),
            'batch_size' => $this->exportBatchSize(),
        ]);
    }

    /**
     * Tahap 2: siapkan satu kelompok FG dan simpan hasilnya sementara di cache.
     */
    public function excelBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'alpha_num', 'size:32'],
            'index' => ['required', 'integer', 'min:0', 'max:'.($this->exportMaxBatches() - 1)],
            'finished_good_ids' => ['required', 'array', 'min:1', 'max:'.$this->exportBatchSize()],
            'finished_good_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $rows = $this->exportBatchRows($validated['finished_good_ids']);

        Cache::put(
            $this->exportCacheKey($request, $validated['token'], (int) $validated['index']),
            $rows,
            now()->addMinutes(30)
        );

        return response()->json(['processed' => count($rows)]);
    }

    /**
     * Tahap 3 (dipanggil dari action excel): gabungkan semua batch sesuai urutan, lalu hapus cache-nya.
     */
    protected function exportBatches(Request $request): Collection
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'alpha_num', 'size:32'],
            'batches' => ['required', 'integer', 'min:0', 'max:'.$this->exportMaxBatches()],
        ]);
        $keys = [];
        $rows = collect();

        for ($index = 0; $index < (int) $validated['batches']; $index++) {
            $keys[] = $key = $this->exportCacheKey($request, $validated['token'], $index);
            $batch = Cache::get($key);

            if ($batch === null) {
                abort(422, __('Data unduhan sudah kedaluwarsa. Silakan unduh ulang.'));
            }

            $rows = $rows->merge($batch);
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        return $rows;
    }

    protected function exportBatchSize(): int
    {
        return 25;
    }

    protected function exportMaxBatches(): int
    {
        return 2000;
    }

    private function exportCacheKey(Request $request, string $token, int $index): string
    {
        return 'excel-export:'.Str::kebab(class_basename($this)).":{$request->user()->id}:{$token}:{$index}";
    }

    abstract protected function exportFinishedGoodIds(Request $request): Collection;

    abstract protected function exportBatchRows(array $finishedGoodIds): array;
}
