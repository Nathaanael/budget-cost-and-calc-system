<?php

namespace App\Console\Commands;

use App\Models\Plant;
use App\Support\LegacyDbfReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ImportLegacyBpFinishedGoods extends Command
{
    protected $signature = 'legacy:import-bp-finished-good
        {--file=prg/FGMAST.DBF : Lokasi file FGMAST.DBF}
        {--plant=2873 : Kode Plant tujuan}
        {--dry-run : Validasi data tanpa menyimpan}';

    protected $description = 'Impor master Finished Good BP dari DBF ke Plant tujuan';

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
            ['Baris DBF', 'Code', 'Description', 'Active', 'Multi Level'],
            $rows->take(20)->map(fn (array $row) => [
                $row['source_row'], $row['code'], $row['description'], $row['active'], $row['multi_level'],
            ]),
        );

        if ($rows->count() > 20) {
            $this->line('Preview menampilkan 20 data pertama dari '.$rows->count().' data.');
        }

        if ($this->option('dry-run')) {
            $this->components->info("Dry-run berhasil: {$rows->count()} Finished Good siap diimpor.");

            return self::SUCCESS;
        }

        $summary = DB::transaction(function () use ($rows, $plant): array {
            $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0];
            $fields = $this->importFields();
            $existing = DB::table('finished_goods')
                ->where('plant_id', $plant->id)
                ->get(['id', 'code', ...$fields])
                ->keyBy('code');
            $usedIds = DB::table('finished_goods')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
            $inserts = [];
            $now = now();

            foreach ($rows as $row) {
                $current = $existing->get($row['code']);
                $values = array_intersect_key($row, array_flip($fields));

                if (! $current) {
                    $id = $this->nextId($usedIds);
                    $usedIds[$id] = true;
                    $inserts[] = [
                        'id' => $id,
                        'plant_id' => $plant->id,
                        'code' => $row['code'],
                        ...$values,
                        'created_by' => null,
                        'updated_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $summary['inserted']++;

                    continue;
                }

                $changed = collect($values)->contains(
                    fn (mixed $value, string $field) => $current->{$field} != $value,
                );

                if ($changed) {
                    DB::table('finished_goods')->where('id', $current->id)->update([
                        ...$values,
                        'updated_at' => $now,
                    ]);
                    $summary['updated']++;
                } else {
                    $summary['unchanged']++;
                }
            }

            foreach (array_chunk($inserts, 200) as $chunk) {
                DB::table('finished_goods')->insert($chunk);
            }

            return $summary;
        });

        $this->components->info('Impor master Finished Good BP berhasil.');
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
        $seenCodes = [];

        foreach ($reader->records($path) as $sourceRow => $record) {
            $data = [
                'source_row' => $sourceRow,
                'code' => strtoupper(trim((string) ($record['FGCODE'] ?? ''))),
                'description' => trim((string) ($record['DESC'] ?? '')),
                'description_1' => $this->nullableString($record['DESC1'] ?? null),
                'product_type_1' => (int) ($record['PRTYPE1'] ?? 0),
                'product_type_2' => (int) ($record['PRTYPE2'] ?? 0),
                'batch' => (int) ($record['BATCH'] ?? 0),
                'selling_price' => $this->number($record['HRGJUAL'] ?? null),
                'multi_level' => strtoupper(trim((string) ($record['LEVEL'] ?? 'N'))),
                'active' => strtoupper(trim((string) ($record['ACTIVE'] ?? 'Y'))),
                'unit_cost_current' => $this->number($record['UC0'] ?? null),
                'unit_cost_le' => $this->number($record['UCLE'] ?? null),
                'unit_cost_qtr_1' => $this->number($record['UC1'] ?? null),
                'unit_cost_qtr_2' => $this->number($record['UC2'] ?? null),
                'unit_cost_qtr_3' => $this->number($record['UC3'] ?? null),
                'unit_cost_qtr_4' => $this->number($record['UC4'] ?? null),
                'unit_price_current' => $this->number($record['PRICE00'] ?? null),
                'unit_price_le' => $this->number($record['PRICELE'] ?? null),
                'unit_price_qtr_1' => $this->number($record['PRICE01'] ?? null),
                'unit_price_qtr_2' => $this->number($record['PRICE02'] ?? null),
                'unit_price_qtr_3' => $this->number($record['PRICE03'] ?? null),
                'unit_price_qtr_4' => $this->number($record['PRICE04'] ?? null),
                'pe_cikampek' => $this->number($record['PECKP'] ?? null),
                'pe_semarang' => $this->number($record['PESMG'] ?? null),
                'pe_surabaya' => $this->number($record['PESBY'] ?? null),
                'pe_palembang' => $this->number($record['PEPLG'] ?? null),
                ...$this->factoryPrices($record),
            ];
            $validator = Validator::make($data, [
                'code' => ['required', 'string', 'max:7', 'regex:/^[A-Z0-9]+$/'],
                'description' => ['required', 'string', 'max:150'],
                'description_1' => ['nullable', 'string', 'max:150'],
                'product_type_1' => ['required', 'integer', 'min:0'],
                'product_type_2' => ['required', 'integer', 'min:0'],
                'batch' => ['nullable', 'integer', 'min:0'],
                'selling_price' => ['nullable', 'numeric', 'min:0'],
                'multi_level' => ['required', 'in:Y,N'],
                'active' => ['required', 'in:Y,N'],
                'unit_cost_*' => ['nullable', 'numeric', 'min:0'],
                'unit_price_*' => ['nullable', 'numeric', 'min:0'],
                'pe_*' => ['nullable', 'numeric', 'min:0'],
            ]);

            if ($validator->fails()) {
                $errors[] = [$sourceRow, $validator->errors()->first()];

                continue;
            }

            if (isset($seenCodes[$data['code']])) {
                $errors[] = [$sourceRow, "Code {$data['code']} duplikat di file sumber."];

                continue;
            }

            $seenCodes[$data['code']] = true;
            $rows->push($data);
        }

        return [$rows, $errors];
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, float>
     */
    private function factoryPrices(array $record): array
    {
        $result = [];
        $periods = ['current' => '0', 'le' => 'LE', 'qtr_1' => '1', 'qtr_2' => '2', 'qtr_3' => '3', 'qtr_4' => '4'];
        $factories = ['semarang' => '1', 'surabaya' => '2', 'palembang' => '3'];

        foreach ($factories as $factory => $prefix) {
            foreach ($periods as $period => $suffix) {
                $source = $suffix === 'LE' ? "PRICELE{$prefix}" : "PRICE{$prefix}{$suffix}";
                $result["unit_price_{$factory}_{$period}"] = $this->number($record[$source] ?? null);
            }
        }

        return $result;
    }

    /** @return array<int, string> */
    private function importFields(): array
    {
        $fields = [
            'description', 'description_1', 'product_type_1', 'product_type_2', 'batch',
            'selling_price', 'multi_level', 'active',
            'unit_cost_current', 'unit_cost_le', 'unit_cost_qtr_1', 'unit_cost_qtr_2', 'unit_cost_qtr_3', 'unit_cost_qtr_4',
            'unit_price_current', 'unit_price_le', 'unit_price_qtr_1', 'unit_price_qtr_2', 'unit_price_qtr_3', 'unit_price_qtr_4',
            'pe_cikampek', 'pe_semarang', 'pe_surabaya', 'pe_palembang',
        ];

        foreach (['semarang', 'surabaya', 'palembang'] as $factory) {
            foreach (['current', 'le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'] as $period) {
                $fields[] = "unit_price_{$factory}_{$period}";
            }
        }

        return $fields;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function number(mixed $value): float
    {
        return $value === null || $value === '' ? 0.0 : (float) $value;
    }

    /** @param array<int, bool> $usedIds */
    private function nextId(array $usedIds): int
    {
        for ($attempt = 0; $attempt < 1000; $attempt++) {
            $id = random_int(10000, 99999);

            if (! isset($usedIds[$id])) {
                return $id;
            }
        }

        throw new RuntimeException('Tidak dapat menghasilkan ID Finished Good unik 5 digit.');
    }
}
