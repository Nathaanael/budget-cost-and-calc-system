<?php

namespace App\Console\Commands;

use App\Models\Plant;
use App\Support\LegacyDbfReader;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportLegacyBpSynonims extends Command
{
    protected $signature = 'legacy:import-bp-synonim
        {--file=prg/SYNONIM.DBF : Lokasi file SYNONIM.DBF}
        {--raw-material-plant=2873 : Plant pemilik Raw Material}
        {--finished-good-plant=2873 : Plant pemilik Finished Good sumber}
        {--dry-run : Validasi data tanpa menyimpan}';

    protected $description = 'Impor Synonim BP dan master Finished Good sumber dari DBF';

    public function handle(LegacyDbfReader $reader): int
    {
        try {
            $path = $this->resolvePath((string) $this->option('file'));
            $rawMaterialPlant = Plant::query()->where('code', (string) $this->option('raw-material-plant'))->firstOrFail();
            $finishedGoodPlant = Plant::query()->where('code', (string) $this->option('finished-good-plant'))->firstOrFail();
            [$rows, $missingRawMaterials, $sourceCount, $duplicateCount] = $this->readRows(
                $reader,
                $path,
                (int) $rawMaterialPlant->id,
            );
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Sumber: {$path}");
        $this->info("Raw Material: Plant {$rawMaterialPlant->code} - {$rawMaterialPlant->description}");
        $this->info("Finished Good sumber: Plant {$finishedGoodPlant->code} - {$finishedGoodPlant->description}");
        $this->table(
            ['Baris DBF', 'Relasi Unik', 'Siap Diimpor', 'Duplikat Digabung', 'RM Tidak Ditemukan'],
            [[$sourceCount, $sourceCount - $duplicateCount, $rows->count(), $duplicateCount, $missingRawMaterials->count()]],
        );
        $this->table(
            ['RM Code', 'RM Description', 'FG Code', 'FG Description'],
            $rows->take(20)->map(fn (array $row) => [
                $row['rm_code'], $row['rm_description'], $row['fg_code'], $row['fg_description'],
            ]),
        );

        if ($rows->count() > 20) {
            $this->line('Preview menampilkan 20 data pertama dari '.$rows->count().' relasi.');
        }

        if ($missingRawMaterials->isNotEmpty()) {
            $this->warn($missingRawMaterials->count().' Raw Material dari Synonim tidak tersedia di master Plant '.$rawMaterialPlant->code.' dan akan dilewati.');
            $this->table(
                ['RM Code', 'Description'],
                $missingRawMaterials->take(20)->map(fn (array $row) => [$row['rm_code'], $row['rm_description']]),
            );

            if ($missingRawMaterials->count() > 20) {
                $this->line('Daftar peringatan menampilkan 20 data pertama.');
            }
        }

        if ($this->option('dry-run')) {
            $this->components->info('Dry-run Synonim BP berhasil. Database tidak diubah.');

            return self::SUCCESS;
        }

        try {
            $summary = DB::transaction(fn (): array => $this->persist(
                $rows,
                (int) $finishedGoodPlant->id,
            ));
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Impor Synonim BP berhasil.');
        $this->table(
            ['Data', 'Inserted', 'Updated', 'Unchanged'],
            [
                ['Finished Good Plant '.$finishedGoodPlant->code, $summary['finished_goods']['inserted'], $summary['finished_goods']['updated'], $summary['finished_goods']['unchanged']],
                ['Synonim', $summary['synonims']['inserted'], $summary['synonims']['updated'], $summary['synonims']['unchanged']],
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

    /**
     * @return array{0: Collection<int, array<string, mixed>>, 1: Collection<int, array<string, string>>, 2: int, 3: int}
     */
    private function readRows(LegacyDbfReader $reader, string $path, int $rawMaterialPlantId): array
    {
        $sourceRows = [];
        $sourceCount = 0;
        $duplicateCount = 0;

        foreach ($reader->records($path) as $sourceRow => $record) {
            $sourceCount++;
            $rawMaterialCode = strtoupper(trim((string) ($record['RMCODE'] ?? '')));
            $finishedGoodCode = strtoupper(trim((string) ($record['FGCODE'] ?? '')));
            $rawMaterialDescription = trim((string) ($record['RMDESC'] ?? ''));
            $finishedGoodDescription = trim((string) ($record['FGDESC'] ?? ''));

            foreach (['Raw Material' => $rawMaterialCode, 'Finished Good' => $finishedGoodCode] as $label => $code) {
                if ($code === '' || strlen($code) > 7 || preg_match('/^[A-Z0-9]+$/', $code) !== 1) {
                    throw new RuntimeException("SYNONIM.DBF baris {$sourceRow}: kode {$label} tidak valid.");
                }
            }

            // Sebagian record lama tidak menyimpan FGDESC. Pada relasi sinonim tersebut,
            // deskripsi RM adalah salinan nama produk sumber dan menjadi fallback teraman.
            $finishedGoodDescription = $finishedGoodDescription !== ''
                ? $finishedGoodDescription
                : $rawMaterialDescription;

            if ($finishedGoodDescription === '') {
                throw new RuntimeException("SYNONIM.DBF baris {$sourceRow}: deskripsi Finished Good dan Raw Material kosong.");
            }

            if (isset($sourceRows[$rawMaterialCode])) {
                if ($sourceRows[$rawMaterialCode]['fg_code'] !== $finishedGoodCode) {
                    throw new RuntimeException("SYNONIM.DBF baris {$sourceRow}: Raw Material {$rawMaterialCode} memiliki lebih dari satu Finished Good.");
                }

                $duplicateCount++;
            }

            // Record terakhir dipakai karena file DBF lama menyimpan beberapa revisi deskripsi sebagai baris tambahan.
            $sourceRows[$rawMaterialCode] = [
                'source_row' => $sourceRow,
                'rm_code' => $rawMaterialCode,
                'rm_description' => $rawMaterialDescription,
                'fg_code' => $finishedGoodCode,
                'fg_description' => $finishedGoodDescription,
            ];
        }

        $rawMaterials = DB::table('raw_materials')
            ->where('plant_id', $rawMaterialPlantId)
            ->get(['id', 'code'])
            ->keyBy('code');
        $rows = collect();
        $missingRawMaterials = collect();

        foreach ($sourceRows as $row) {
            $rawMaterial = $rawMaterials->get($row['rm_code']);

            if (! $rawMaterial) {
                $missingRawMaterials->push($row);

                continue;
            }

            $rows->push([
                ...$row,
                'raw_material_id' => (int) $rawMaterial->id,
            ]);
        }

        return [$rows->values(), $missingRawMaterials->values(), $sourceCount, $duplicateCount];
    }

    /** @return array<string, array{inserted: int, updated: int, unchanged: int}> */
    private function persist(Collection $rows, int $finishedGoodPlantId): array
    {
        $summary = [
            'finished_goods' => ['inserted' => 0, 'updated' => 0, 'unchanged' => 0],
            'synonims' => ['inserted' => 0, 'updated' => 0, 'unchanged' => 0],
        ];
        $now = now();
        $finishedGoodRows = $rows
            ->keyBy('fg_code')
            ->map(fn (array $row) => [
                'code' => $row['fg_code'],
                'description' => $row['fg_description'],
            ]);
        $existingFinishedGoods = DB::table('finished_goods')
            ->where('plant_id', $finishedGoodPlantId)
            ->get(['id', 'code', 'description'])
            ->keyBy('code');
        $usedFinishedGoodIds = DB::table('finished_goods')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $finishedGoodIds = [];
        $finishedGoodInserts = [];

        foreach ($finishedGoodRows as $code => $row) {
            $current = $existingFinishedGoods->get($code);

            if (! $current) {
                $id = $this->nextId($usedFinishedGoodIds, 'Finished Good');
                $usedFinishedGoodIds[$id] = true;
                $finishedGoodIds[$code] = $id;
                $finishedGoodInserts[] = [
                    'id' => $id,
                    'plant_id' => $finishedGoodPlantId,
                    'code' => $code,
                    'description' => $row['description'],
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $summary['finished_goods']['inserted']++;
            } else {
                $finishedGoodIds[$code] = (int) $current->id;

                if ($current->description !== $row['description']) {
                    DB::table('finished_goods')->where('id', $current->id)->update([
                        'description' => $row['description'],
                        'updated_at' => $now,
                    ]);
                    $summary['finished_goods']['updated']++;
                } else {
                    $summary['finished_goods']['unchanged']++;
                }
            }
        }

        foreach (array_chunk($finishedGoodInserts, 200) as $chunk) {
            DB::table('finished_goods')->insert($chunk);
        }

        $existingSynonims = DB::table('synonims')
            ->whereIn('raw_material_id', $rows->pluck('raw_material_id'))
            ->get(['id', 'raw_material_id', 'finished_good_id'])
            ->keyBy('raw_material_id');
        $usedSynonimIds = DB::table('synonims')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $synonimInserts = [];

        foreach ($rows as $row) {
            $current = $existingSynonims->get($row['raw_material_id']);
            $finishedGoodId = $finishedGoodIds[$row['fg_code']];

            if (! $current) {
                $id = $this->nextId($usedSynonimIds, 'Synonim');
                $usedSynonimIds[$id] = true;
                $synonimInserts[] = [
                    'id' => $id,
                    'raw_material_id' => $row['raw_material_id'],
                    'finished_good_id' => $finishedGoodId,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $summary['synonims']['inserted']++;
            } elseif ((int) $current->finished_good_id !== $finishedGoodId) {
                DB::table('synonims')->where('id', $current->id)->update([
                    'finished_good_id' => $finishedGoodId,
                    'updated_at' => $now,
                ]);
                $summary['synonims']['updated']++;
            } else {
                $summary['synonims']['unchanged']++;
            }
        }

        foreach (array_chunk($synonimInserts, 200) as $chunk) {
            DB::table('synonims')->insert($chunk);
        }

        return $summary;
    }

    /** @param array<int, bool> $usedIds */
    private function nextId(array $usedIds, string $label): int
    {
        for ($attempt = 0; $attempt < 1000; $attempt++) {
            $id = random_int(10000, 99999);

            if (! isset($usedIds[$id])) {
                return $id;
            }
        }

        throw new RuntimeException("Tidak dapat menghasilkan ID internal {$label} unik 5 digit.");
    }
}
