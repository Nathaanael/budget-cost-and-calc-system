<?php

namespace App\Console\Commands;

use App\Models\Plant;
use App\Support\LegacyDbfReader;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportLegacyBpFormulas extends Command
{
    protected $signature = 'legacy:import-bp-formulas
        {--noodle-file=prg/NDLFORM.DBF : Lokasi file formula Noodle}
        {--finished-good-file=prg/FORMULA.DBF : Lokasi file formula Finished Good}
        {--plant=2873 : Kode Plant tujuan}
        {--dry-run : Validasi dan tampilkan ringkasan tanpa menyimpan}';

    protected $description = 'Impor Formula Noodle dan Formula Finished Good BP dari DBF';

    public function handle(LegacyDbfReader $reader): int
    {
        try {
            $plant = Plant::query()->where('code', (string) $this->option('plant'))->firstOrFail();
            $noodlePath = $this->resolvePath((string) $this->option('noodle-file'));
            $finishedGoodPath = $this->resolvePath((string) $this->option('finished-good-file'));
            $catalogs = $this->catalogs((int) $plant->id);
            [$noodleGroups, $noodleStats, $noodleWarnings] = $this->readNoodleFormulas($reader, $noodlePath, $catalogs);
            [$finishedGoodGroups, $finishedGoodStats, $finishedGoodWarnings] = $this->readFinishedGoodFormulas($reader, $finishedGoodPath, $catalogs);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Tujuan: Plant {$plant->code} - {$plant->description}");
        $this->line("Formula Noodle: {$noodlePath}");
        $this->line("Formula Finished Good: {$finishedGoodPath}");
        $this->table(
            ['Jenis', 'Baris DBF', 'Formula Siap', 'Item Siap', 'Baris Dilewati', 'Duplikat Digabung'],
            [
                ['Formula Noodle', $noodleStats['source_rows'], $noodleGroups->count(), $noodleStats['ready_items'], $noodleStats['skipped_rows'], 0],
                ['Formula FG', $finishedGoodStats['source_rows'], $finishedGoodGroups->count(), $finishedGoodStats['ready_items'], $finishedGoodStats['skipped_rows'], $finishedGoodStats['merged_duplicates']],
            ],
        );

        $warnings = [...$noodleWarnings, ...$finishedGoodWarnings];

        if ($warnings !== []) {
            $this->warn('Beberapa referensi legacy dilewati karena master-nya tidak tersedia di Plant tujuan.');
            $this->table(['Jenis', 'Kode', 'Jumlah Baris'], $warnings);
        }

        if ($this->option('dry-run')) {
            $this->components->info('Dry-run formula BP berhasil. Database tidak diubah.');

            return self::SUCCESS;
        }

        $summary = DB::transaction(function () use ($noodleGroups, $finishedGoodGroups, $plant): array {
            return [
                'noodle' => $this->persistFormulas(
                    $noodleGroups,
                    'noodle_formulas',
                    'noodle_formula_items',
                    'noodle_id',
                    'finished_good_id',
                    (int) $plant->id,
                ),
                'finished_good' => $this->persistFormulas(
                    $finishedGoodGroups,
                    'finished_good_formulas',
                    'finished_good_formula_items',
                    'finished_good_id',
                    'raw_material_id',
                ),
            ];
        });

        $this->components->info('Impor Formula Noodle dan Formula Finished Good BP berhasil.');
        $this->table(
            ['Jenis', 'Inserted', 'Updated', 'Unchanged', 'Item Aktif'],
            [
                ['Formula Noodle', ...array_values($summary['noodle'])],
                ['Formula FG', ...array_values($summary['finished_good'])],
            ],
        );

        return self::SUCCESS;
    }

    private function resolvePath(string $file): string
    {
        $path = realpath(base_path($file));

        if ($path === false || ! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'dbf') {
            throw new RuntimeException("File DBF tidak ditemukan atau tidak valid: {$file}");
        }

        return $path;
    }

    /** @return array<string, Collection<string, int>> */
    private function catalogs(int $plantId): array
    {
        return [
            'noodles' => DB::table('noodles')->where('plant_id', $plantId)->pluck('id', 'code'),
            'finished_goods' => DB::table('finished_goods')->where('plant_id', $plantId)->pluck('id', 'code'),
            'raw_materials' => DB::table('raw_materials')->where('plant_id', $plantId)->pluck('id', 'code'),
        ];
    }

    /**
     * @param  array<string, Collection<string, int>>  $catalogs
     * @return array{0: Collection<int, Collection<int, array<string, mixed>>>, 1: array<string, int>, 2: array<int, array<int, mixed>>}
     */
    private function readNoodleFormulas(LegacyDbfReader $reader, string $path, array $catalogs): array
    {
        $groups = collect();
        $missingNoodles = [];
        $missingFinishedGoods = [];
        $sourceRows = 0;
        $skippedRows = 0;

        foreach ($reader->records($path) as $sourceRow => $record) {
            $sourceRows++;
            $noodleCode = strtoupper(trim((string) ($record['NDLCODE'] ?? '')));
            $finishedGoodCode = strtoupper(trim((string) ($record['FGCODE'] ?? '')));
            $standard = $this->standard($record['STANDARD'] ?? null, $sourceRow, 'NDLFORM');
            $noodleId = $catalogs['noodles']->get($noodleCode);
            $finishedGoodId = $catalogs['finished_goods']->get($finishedGoodCode);

            if (! $noodleId) {
                $missingNoodles[$noodleCode] = ($missingNoodles[$noodleCode] ?? 0) + 1;
            }

            if (! $finishedGoodId) {
                $missingFinishedGoods[$finishedGoodCode] = ($missingFinishedGoods[$finishedGoodCode] ?? 0) + 1;
            }

            if (! $noodleId || ! $finishedGoodId) {
                $skippedRows++;

                continue;
            }

            $items = $groups->get((int) $noodleId, collect());
            $items->push([
                'component_id' => (int) $finishedGoodId,
                'standard' => $standard,
                'source_row' => $sourceRow,
            ]);
            $groups->put((int) $noodleId, $items);
        }

        $groups = $groups->map(fn (Collection $items) => $items
            ->sortBy('source_row')
            ->values()
            ->map(fn (array $item, int $index) => [...$item, 'position' => $index + 1]));

        return [
            $groups,
            [
                'source_rows' => $sourceRows,
                'ready_items' => $groups->sum(fn (Collection $items) => $items->count()),
                'skipped_rows' => $skippedRows,
                'merged_duplicates' => 0,
            ],
            [
                ...$this->warnings('Noodle tidak ditemukan', $missingNoodles),
                ...$this->warnings('FG komponen Noodle tidak ditemukan', $missingFinishedGoods),
            ],
        ];
    }

    /**
     * @param  array<string, Collection<string, int>>  $catalogs
     * @return array{0: Collection<int, Collection<int, array<string, mixed>>>, 1: array<string, int>, 2: array<int, array<int, mixed>>}
     */
    private function readFinishedGoodFormulas(LegacyDbfReader $reader, string $path, array $catalogs): array
    {
        $groups = collect();
        $missingFinishedGoods = [];
        $missingRawMaterials = [];
        $sourceRows = 0;
        $skippedRows = 0;
        $mergedDuplicates = 0;

        foreach ($reader->records($path) as $sourceRow => $record) {
            $sourceRows++;
            $finishedGoodCode = strtoupper(trim((string) ($record['FGCODE'] ?? '')));
            $rawMaterialCode = strtoupper(trim((string) ($record['RMCODE'] ?? '')));
            $standard = $this->standard($record['STANDARDB'] ?? null, $sourceRow, 'FORMULA');
            $finishedGoodId = $catalogs['finished_goods']->get($finishedGoodCode);
            $rawMaterialId = $catalogs['raw_materials']->get($rawMaterialCode);

            if (! $finishedGoodId) {
                $missingFinishedGoods[$finishedGoodCode] = ($missingFinishedGoods[$finishedGoodCode] ?? 0) + 1;
            }

            if (! $rawMaterialId) {
                $missingRawMaterials[$rawMaterialCode] = ($missingRawMaterials[$rawMaterialCode] ?? 0) + 1;
            }

            if (! $finishedGoodId || ! $rawMaterialId) {
                $skippedRows++;

                continue;
            }

            $items = $groups->get((int) $finishedGoodId, collect());
            $componentId = (int) $rawMaterialId;
            $existing = $items->get($componentId);

            if ($existing) {
                $existing['standard'] = round($existing['standard'] + $standard, 6);
                $existing['sequence'] = min($existing['sequence'], (int) ($record['SEQ'] ?? $sourceRow));
                $items->put($componentId, $existing);
                $mergedDuplicates++;
            } else {
                $items->put($componentId, [
                    'component_id' => $componentId,
                    'standard' => $standard,
                    'sequence' => (int) ($record['SEQ'] ?? $sourceRow),
                    'source_row' => $sourceRow,
                ]);
            }

            $groups->put((int) $finishedGoodId, $items);
        }

        $groups = $groups->map(fn (Collection $items) => $items
            ->sortBy(fn (array $item) => [$item['sequence'], $item['source_row']])
            ->values()
            ->map(fn (array $item, int $index) => [...$item, 'position' => $index + 1]));

        return [
            $groups,
            [
                'source_rows' => $sourceRows,
                'ready_items' => $groups->sum(fn (Collection $items) => $items->count()),
                'skipped_rows' => $skippedRows,
                'merged_duplicates' => $mergedDuplicates,
            ],
            [
                ...$this->warnings('FG induk tidak ditemukan', $missingFinishedGoods),
                ...$this->warnings('RM komponen FG tidak ditemukan', $missingRawMaterials),
            ],
        ];
    }

    /**
     * @param  Collection<int, Collection<int, array<string, mixed>>>  $groups
     * @return array{inserted: int, updated: int, unchanged: int, items: int}
     */
    private function persistFormulas(
        Collection $groups,
        string $formulaTable,
        string $itemTable,
        string $parentColumn,
        string $componentColumn,
        ?int $plantId = null,
    ): array {
        $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'items' => 0];
        $headers = DB::table($formulaTable)->whereIn($parentColumn, $groups->keys())->get()->keyBy($parentColumn);
        $headerIds = $headers->pluck('id');
        $existingItems = DB::table($itemTable)
            ->whereIn($this->formulaForeignKey($formulaTable), $headerIds)
            ->whereNull('deleted_at')
            ->orderBy('position')
            ->get()
            ->groupBy($this->formulaForeignKey($formulaTable));
        $usedHeaderIds = DB::table($formulaTable)->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $usedItemIds = DB::table($itemTable)->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $headerInserts = [];
        $itemInserts = [];
        $now = now();
        $foreignKey = $this->formulaForeignKey($formulaTable);

        foreach ($groups as $parentId => $items) {
            $header = $headers->get($parentId);
            $formulaId = $header ? (int) $header->id : $this->reserveId($usedHeaderIds);
            $same = $header && $header->deleted_at === null && $this->sameItems(
                $existingItems->get($formulaId, collect()),
                $items,
                $componentColumn,
            );

            if ($same) {
                $summary['unchanged']++;
                $summary['items'] += $items->count();

                continue;
            }

            if ($header) {
                DB::table($formulaTable)->where('id', $formulaId)->update([
                    'updated_by' => null,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
                DB::table($itemTable)->where($foreignKey, $formulaId)->delete();
                $summary['updated']++;
            } else {
                $headerInserts[] = array_filter([
                    'id' => $formulaId,
                    $parentColumn => $parentId,
                    'plant_id' => $plantId,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ], fn (mixed $value, string $key) => $key !== 'plant_id' || $value !== null, ARRAY_FILTER_USE_BOTH);
                $summary['inserted']++;
            }

            foreach ($items as $item) {
                $itemInserts[] = [
                    'id' => $this->reserveId($usedItemIds),
                    $foreignKey => $formulaId,
                    $componentColumn => $item['component_id'],
                    'standard' => $item['standard'],
                    'position' => $item['position'],
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
            }

            $summary['items'] += $items->count();
        }

        foreach (array_chunk($headerInserts, 500) as $chunk) {
            DB::table($formulaTable)->insert($chunk);
        }

        foreach (array_chunk($itemInserts, 500) as $chunk) {
            DB::table($itemTable)->insert($chunk);
        }

        return $summary;
    }

    private function sameItems(Collection $existing, Collection $source, string $componentColumn): bool
    {
        if ($existing->count() !== $source->count()) {
            return false;
        }

        return $source->values()->every(function (array $item, int $index) use ($existing, $componentColumn): bool {
            $current = $existing->values()->get($index);

            return $current
                && (int) $current->{$componentColumn} === $item['component_id']
                && (int) $current->position === $item['position']
                && round((float) $current->standard, 6) === round((float) $item['standard'], 6);
        });
    }

    private function formulaForeignKey(string $formulaTable): string
    {
        return $formulaTable === 'noodle_formulas' ? 'noodle_formula_id' : 'finished_good_formula_id';
    }

    private function standard(mixed $value, int $sourceRow, string $source): float
    {
        if (! is_numeric($value) || (float) $value < 0 || (float) $value > 999999999999.999999) {
            throw new RuntimeException("Nilai standard tidak valid pada {$source} baris {$sourceRow}.");
        }

        return round((float) $value, 6);
    }

    /** @param array<string, int> $codes */
    private function warnings(string $type, array $codes): array
    {
        ksort($codes);

        return collect($codes)->map(fn (int $count, string $code) => [$type, $code, $count])->values()->all();
    }

    /** @param array<int, bool> $usedIds */
    private function reserveId(array &$usedIds): int
    {
        for ($attempt = 0; $attempt < 2000; $attempt++) {
            $id = random_int(10000, 99999);

            if (! isset($usedIds[$id])) {
                $usedIds[$id] = true;

                return $id;
            }
        }

        throw new RuntimeException('Tidak dapat menghasilkan ID formula unik 5 digit.');
    }
}
