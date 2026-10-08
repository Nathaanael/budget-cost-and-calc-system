<?php

namespace App\Console\Commands;

use App\Models\Plant;
use App\Support\LegacyDbfReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ImportLegacyBpNoodles extends Command
{
    protected $signature = 'legacy:import-bp-noodle
        {--file=prg/NDLMAST.DBF : Lokasi file NDLMAST.DBF}
        {--plant=2873 : Kode Plant tujuan}
        {--dry-run : Validasi data tanpa menyimpan}';

    protected $description = 'Impor master Noodle BP dari DBF ke Plant tujuan';

    public function handle(LegacyDbfReader $reader): int
    {
        try {
            $path = $this->resolvePath((string) $this->option('file'));
            $plant = Plant::query()->where('code', (string) $this->option('plant'))->firstOrFail();
            [$rows, $errors] = $this->validatedRows($reader, $path);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Sumber: {$path}");
        $this->info("Tujuan: Plant {$plant->code} - {$plant->description}");
        $this->line("Record aktif terbaca: {$rows->count()}");

        if ($errors !== []) {
            $this->error('Validasi gagal. Database tidak diubah.');
            $this->table(['Baris DBF', 'Masalah'], $errors);

            return self::FAILURE;
        }

        $this->table(
            ['Baris DBF', 'Code', 'Description', 'Unit'],
            $rows->take(20)->map(fn (array $row) => [
                $row['source_row'], $row['code'], $row['description'], $row['unit'],
            ]),
        );

        if ($rows->count() > 20) {
            $this->line('Preview menampilkan 20 data pertama dari '.$rows->count().' data.');
        }

        if ($this->option('dry-run')) {
            $this->components->info("Dry-run berhasil: {$rows->count()} Noodle siap diimpor.");

            return self::SUCCESS;
        }

        $summary = DB::transaction(function () use ($rows, $plant): array {
            $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0];
            $existing = DB::table('noodles')
                ->where('plant_id', $plant->id)
                ->get(['id', 'code', 'description', 'unit'])
                ->keyBy('code');
            $usedIds = DB::table('noodles')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
            $inserts = [];
            $now = now();

            foreach ($rows as $row) {
                $current = $existing->get($row['code']);

                if (! $current) {
                    $id = $this->nextId($usedIds);
                    $usedIds[$id] = true;
                    $inserts[] = [
                        'id' => $id,
                        'plant_id' => $plant->id,
                        'code' => $row['code'],
                        'description' => $row['description'],
                        'unit' => $row['unit'],
                        'created_by' => null,
                        'updated_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $summary['inserted']++;
                } elseif ($current->description !== $row['description'] || $current->unit !== $row['unit']) {
                    DB::table('noodles')->where('id', $current->id)->update([
                        'description' => $row['description'],
                        'unit' => $row['unit'],
                        'updated_at' => $now,
                    ]);
                    $summary['updated']++;
                } else {
                    $summary['unchanged']++;
                }
            }

            foreach (array_chunk($inserts, 250) as $chunk) {
                DB::table('noodles')->insert($chunk);
            }

            return $summary;
        });

        $this->components->info('Impor master Noodle BP berhasil.');
        $this->table(['Inserted', 'Updated', 'Unchanged', 'Total'], [[
            $summary['inserted'], $summary['updated'], $summary['unchanged'], $rows->count(),
        ]]);

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
     * @return array{0: \Illuminate\Support\Collection<int, array<string, mixed>>, 1: array<int, array<int, mixed>>}
     */
    private function validatedRows(LegacyDbfReader $reader, string $path): array
    {
        $rows = collect();
        $errors = [];

        foreach ($reader->records($path) as $sourceRow => $record) {
            $data = [
                'source_row' => $sourceRow,
                'code' => strtoupper(trim((string) ($record['NDLCODE'] ?? ''))),
                'description' => trim((string) ($record['DESC'] ?? '')),
                'unit' => trim((string) ($record['UNIT'] ?? '')),
            ];
            $validator = Validator::make($data, [
                'code' => ['required', 'string', 'max:8', 'regex:/^[A-Z0-9]+$/'],
                'description' => ['required', 'string', 'max:150'],
                'unit' => ['required', 'string', 'max:30'],
            ]);

            if ($validator->fails()) {
                $errors[] = [$sourceRow, $validator->errors()->first()];
                continue;
            }

            if ($rows->contains('code', $data['code'])) {
                $errors[] = [$sourceRow, "Code {$data['code']} duplikat di file sumber."];
                continue;
            }

            $rows->push($data);
        }

        return [$rows, $errors];
    }

    /**
     * @param  array<int, bool>  $usedIds
     */
    private function nextId(array $usedIds): int
    {
        for ($attempt = 0; $attempt < 1000; $attempt++) {
            $id = random_int(10000, 99999);

            if (! isset($usedIds[$id])) {
                return $id;
            }
        }

        throw new RuntimeException('Tidak dapat menghasilkan ID Noodle unik 5 digit.');
    }
}
