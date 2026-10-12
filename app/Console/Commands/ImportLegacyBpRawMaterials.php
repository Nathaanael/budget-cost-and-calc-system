<?php

namespace App\Console\Commands;

use App\Models\Plant;
use App\Support\LegacyDbfReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ImportLegacyBpRawMaterials extends Command
{
    private const PERIODS = [
        'current' => ['USD0', 'RP0'],
        'le' => ['USDLE', 'RPLE'],
        'qtr_1' => ['USD1', 'RP1'],
        'qtr_2' => ['USD2', 'RP2'],
        'qtr_3' => ['USD3', 'RP3'],
        'qtr_4' => ['USD4', 'RP4'],
    ];

    protected $signature = 'legacy:import-bp-raw-material
        {--file=prg/RMMAST.DBF : Lokasi file RMMAST.DBF}
        {--plant=2873 : Kode Plant tujuan}
        {--dry-run : Validasi data tanpa menyimpan}';

    protected $description = 'Impor master Raw Material dan harga BP dari DBF ke Plant tujuan';

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
            ['Baris DBF', 'Code', 'Description', 'Unit', 'ID', 'Currency'],
            $rows->take(20)->map(fn (array $row) => [
                $row['source_row'], $row['code'], $row['description'], $row['unit'],
                $row['material_id'], $row['currency_type'],
            ]),
        );

        if ($rows->count() > 20) {
            $this->line('Preview menampilkan 20 data pertama dari '.$rows->count().' data.');
        }

        if ($this->option('dry-run')) {
            $this->components->info("Dry-run berhasil: {$rows->count()} Raw Material dan ".$rows->count() * count(self::PERIODS).' harga periodik siap diimpor.');

            return self::SUCCESS;
        }

        $summary = DB::transaction(fn (): array => $this->persist($rows, (int) $plant->id));

        $this->components->info('Impor master Raw Material BP berhasil.');
        $this->table(['Inserted', 'Updated', 'Unchanged', 'Total RM', 'Harga Periodik'], [[
            $summary['inserted'], $summary['updated'], $summary['unchanged'], $rows->count(), $summary['prices'],
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
            $currencyCode = trim((string) ($record['TYPE_CURR'] ?? ''));
            $data = [
                'source_row' => $sourceRow,
                'code' => strtoupper(trim((string) ($record['RMCODE'] ?? ''))),
                'material_id' => $this->nullableString($record['ID'] ?? null),
                'description' => trim((string) ($record['DESC'] ?? '')),
                'unit' => strtoupper(trim((string) ($record['UNIT'] ?? ''))),
                'wastage_all' => $this->number($record['WASTE'] ?? null),
                'currency_type' => match ($currencyCode) {
                    '1' => 'USD',
                    '2' => 'Rp',
                    default => $currencyCode,
                },
                'type_rm' => $this->nullableString($record['TYPE'] ?? null),
                'prices' => $this->prices($record),
            ];
            $validator = Validator::make($data, [
                'code' => ['required', 'string', 'max:7', 'regex:/^[A-Z0-9]+$/'],
                'material_id' => ['nullable', 'string', 'max:50'],
                'description' => ['required', 'string', 'max:150'],
                'unit' => ['required', 'string', 'max:30'],
                'wastage_all' => ['required', 'numeric', 'min:0', 'max:999999.9999'],
                'currency_type' => ['required', 'in:Rp,USD'],
                'type_rm' => ['nullable', 'string', 'max:50'],
                'prices.*.usd_amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
                'prices.*.rupiah_amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
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
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $rows
     * @return array{inserted: int, updated: int, unchanged: int, prices: int}
     */
    private function persist($rows, int $plantId): array
    {
        $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'prices' => 0];
        $fields = ['material_id', 'description', 'unit', 'wastage_all', 'currency_type', 'type_rm'];
        $existing = DB::table('raw_materials')
            ->where('plant_id', $plantId)
            ->get(['id', 'code', ...$fields])
            ->keyBy('code');
        $usedIds = DB::table('raw_materials')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $materialIds = [];
        $inserts = [];
        $now = now();

        foreach ($rows as $row) {
            $current = $existing->get($row['code']);
            $values = array_intersect_key($row, array_flip($fields));

            if (! $current) {
                $id = $this->nextId($usedIds);
                $usedIds[$id] = true;
                $materialIds[$row['code']] = $id;
                $inserts[] = [
                    'id' => $id,
                    'plant_id' => $plantId,
                    'code' => $row['code'],
                    ...$values,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $summary['inserted']++;
            } else {
                $materialIds[$row['code']] = (int) $current->id;
                $changed = collect($values)->contains(
                    fn (mixed $value, string $field) => $current->{$field} != $value,
                );

                if ($changed) {
                    DB::table('raw_materials')->where('id', $current->id)->update([
                        ...$values,
                        'updated_at' => $now,
                    ]);
                    $summary['updated']++;
                } else {
                    $summary['unchanged']++;
                }
            }
        }

        foreach (array_chunk($inserts, 250) as $chunk) {
            DB::table('raw_materials')->insert($chunk);
        }

        $priceRows = [];

        foreach ($rows as $row) {
            foreach ($row['prices'] as $period => $price) {
                $priceRows[] = [
                    'raw_material_id' => $materialIds[$row['code']],
                    'period' => $period,
                    ...$price,
                    'source_kind' => 'legacy_dbf',
                    'reference_id' => null,
                    'exchange_rate' => null,
                    'calculated_at' => null,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($priceRows, 500) as $chunk) {
            DB::table('raw_material_prices')->upsert(
                $chunk,
                ['raw_material_id', 'period'],
                ['usd_amount', 'rupiah_amount', 'source_kind', 'reference_id', 'exchange_rate', 'calculated_at', 'updated_by', 'updated_at'],
            );
        }

        $summary['prices'] = count($priceRows);

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, array{usd_amount: float, rupiah_amount: float}>
     */
    private function prices(array $record): array
    {
        $prices = [];

        foreach (self::PERIODS as $period => [$usdField, $rupiahField]) {
            $prices[$period] = [
                'usd_amount' => $this->number($record[$usdField] ?? null),
                'rupiah_amount' => $this->number($record[$rupiahField] ?? null),
            ];
        }

        return $prices;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = strtoupper(trim((string) $value));

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

        throw new RuntimeException('Tidak dapat menghasilkan ID internal Raw Material unik 5 digit.');
    }
}
