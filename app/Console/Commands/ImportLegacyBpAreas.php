<?php

namespace App\Console\Commands;

use App\Models\AreaNoodle;
use App\Models\Plant;
use App\Support\LegacyDbfReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ImportLegacyBpAreas extends Command
{
    protected $signature = 'legacy:import-bp-area
        {--file=prg/AREA.DBF : Lokasi file AREA.DBF}
        {--plant=2873 : Kode Plant tujuan}
        {--dry-run : Validasi dan tampilkan data tanpa menyimpan}';

    protected $description = 'Impor master Area Noodle BP dari DBF ke Plant tujuan';

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
        $this->table(['Baris DBF', 'Code', 'Description'], $rows->map(fn (array $row) => [
            $row['source_row'], $row['code'], $row['description'],
        ]));

        if ($errors !== []) {
            $this->error('Validasi gagal. Database tidak diubah.');
            $this->table(['Baris DBF', 'Masalah'], $errors);

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->components->info("Dry-run berhasil: {$rows->count()} Area siap diimpor.");

            return self::SUCCESS;
        }

        $summary = DB::transaction(function () use ($rows, $plant): array {
            $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0];

            foreach ($rows as $row) {
                $area = AreaNoodle::withoutGlobalScope('plant')->firstOrNew([
                    'plant_id' => $plant->id,
                    'code' => $row['code'],
                ]);
                $wasExisting = $area->exists;
                $area->description = $row['description'];

                if (! $wasExisting) {
                    $area->save();
                    $summary['inserted']++;
                } elseif ($area->isDirty()) {
                    $area->save();
                    $summary['updated']++;
                } else {
                    $summary['unchanged']++;
                }
            }

            return $summary;
        });

        $this->components->info('Impor Area BP berhasil.');
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
                'code' => strtoupper(trim((string) ($record['CODE'] ?? ''))),
                'description' => trim((string) ($record['DESC'] ?? '')),
            ];
            $validator = Validator::make($data, [
                'code' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9]+$/'],
                'description' => ['required', 'string', 'max:150'],
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
}
