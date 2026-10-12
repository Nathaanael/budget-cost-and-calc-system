<?php

namespace App\Console\Commands;

use App\Models\Plant;
use App\Support\LegacyDbfReader;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportLegacyBpVolumeNoodles extends Command
{
    private const FIELD_MAP = [
        'le_july' => 'LEJUL',
        'le_august' => 'LEAGT',
        'le_september' => 'LESEP',
        'le_october' => 'LEOKT',
        'le_november' => 'LENOV',
        'le_december' => 'LEDES',
        'january' => 'JAN',
        'february' => 'FEB',
        'march' => 'MAR',
        'april' => 'APR',
        'may' => 'MEI',
        'june' => 'JUN',
        'july' => 'JUL',
        'august' => 'AGT',
        'september' => 'SEP',
        'october' => 'OKT',
        'november' => 'NOV',
        'december' => 'DES',
    ];

    private const LE_FIELDS = ['le_july', 'le_august', 'le_september', 'le_october', 'le_november', 'le_december'];

    private const MONTH_FIELDS = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

    protected $signature = 'legacy:import-bp-volume-noodle
        {--file=prg/VOLNDL.DBF : Lokasi file VOLNDL.DBF}
        {--plant=2873 : Kode Plant tujuan}
        {--dry-run : Validasi data tanpa menyimpan}';

    protected $description = 'Impor Entry Volume Noodle BP dari DBF ke Plant tujuan';

    public function handle(LegacyDbfReader $reader): int
    {
        try {
            $path = $this->resolvePath((string) $this->option('file'));
            $plant = Plant::query()->where('code', (string) $this->option('plant'))->firstOrFail();
            [$rows, $warnings, $sourceCount] = $this->readRows($reader, $path, (int) $plant->id);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Sumber: {$path}");
        $this->info("Tujuan: Plant {$plant->code} - {$plant->description}");
        $this->table(
            ['Baris DBF', 'Volume Siap', 'Baris Dilewati'],
            [[$sourceCount, $rows->count(), array_sum($warnings)]],
        );
        $this->table(
            ['Area', 'Noodle', 'LE Total', 'AOP Total'],
            $rows->take(20)->map(fn (array $row) => [
                $row['area_code'], $row['noodle_code'], $row['total_le'], $row['total_aop'],
            ]),
        );

        if ($rows->count() > 20) {
            $this->line('Preview menampilkan 20 data pertama dari '.$rows->count().' data.');
        }

        if ($warnings !== []) {
            $this->warn('Beberapa baris dilewati karena Noodle tidak tersedia pada Plant tujuan.');
            $this->table(
                ['Kode Noodle', 'Jumlah Baris'],
                collect($warnings)->map(fn (int $count, string $code) => [$code, $count])->values(),
            );
        }

        if ($this->option('dry-run')) {
            $this->components->info('Dry-run Volume Noodle BP berhasil. Database tidak diubah.');

            return self::SUCCESS;
        }

        $summary = DB::transaction(fn (): array => $this->persist($rows, (int) $plant->id));

        $this->components->info('Impor Entry Volume Noodle BP berhasil.');
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
     * @return array{0: Collection<int, array<string, mixed>>, 1: array<string, int>, 2: int}
     */
    private function readRows(LegacyDbfReader $reader, string $path, int $plantId): array
    {
        $areas = DB::table('area_noodles')->where('plant_id', $plantId)->pluck('id', 'code');
        $noodles = DB::table('noodles')->where('plant_id', $plantId)->pluck('id', 'code');
        $rows = collect();
        $warnings = [];
        $seenPairs = [];
        $sourceCount = 0;

        foreach ($reader->records($path) as $sourceRow => $record) {
            $sourceCount++;
            $areaCode = strtoupper(trim((string) ($record['CODE'] ?? '')));
            $noodleCode = strtoupper(trim((string) ($record['NDLCODE'] ?? '')));
            $areaId = $areas->get($areaCode);
            $noodleId = $noodles->get($noodleCode);

            if (! $areaId) {
                throw new RuntimeException("VOLNDL.DBF baris {$sourceRow}: Area {$areaCode} tidak tersedia pada Plant tujuan.");
            }

            if (! $noodleId) {
                $warnings[$noodleCode] = ($warnings[$noodleCode] ?? 0) + 1;

                continue;
            }

            $pair = "{$areaId}:{$noodleId}";

            if (isset($seenPairs[$pair])) {
                throw new RuntimeException("VOLNDL.DBF baris {$sourceRow}: kombinasi {$areaCode} / {$noodleCode} duplikat.");
            }

            $seenPairs[$pair] = true;
            $data = [
                'area_code' => $areaCode,
                'noodle_code' => $noodleCode,
                'area_noodle_id' => (int) $areaId,
                'noodle_id' => (int) $noodleId,
            ];

            foreach (self::FIELD_MAP as $target => $source) {
                $data[$target] = $this->volume($record[$source] ?? null, $sourceRow, $source);
            }

            $data['total_le'] = collect(self::LE_FIELDS)->sum(fn (string $field) => $data[$field]);
            $data['total_aop'] = collect(self::MONTH_FIELDS)->sum(fn (string $field) => $data[$field]);
            $rows->push($data);
        }

        ksort($warnings);

        return [$rows, $warnings, $sourceCount];
    }

    /** @return array{inserted: int, updated: int, unchanged: int} */
    private function persist(Collection $rows, int $plantId): array
    {
        $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0];
        $fields = [...array_keys(self::FIELD_MAP), 'total_le', 'total_aop'];
        $existing = DB::table('volume_noodles')
            ->where('plant_id', $plantId)
            ->get(['id', 'area_noodle_id', 'noodle_id', ...$fields])
            ->keyBy(fn (object $row) => "{$row->area_noodle_id}:{$row->noodle_id}");
        $inserts = [];
        $now = now();

        foreach ($rows as $row) {
            $key = "{$row['area_noodle_id']}:{$row['noodle_id']}";
            $current = $existing->get($key);
            $values = array_intersect_key($row, array_flip($fields));

            if (! $current) {
                $inserts[] = [
                    'plant_id' => $plantId,
                    'area_noodle_id' => $row['area_noodle_id'],
                    'noodle_id' => $row['noodle_id'],
                    ...$values,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $summary['inserted']++;
            } elseif ($this->changed($current, $values)) {
                DB::table('volume_noodles')->where('id', $current->id)->update([
                    ...$values,
                    'updated_by' => null,
                    'updated_at' => $now,
                ]);
                $summary['updated']++;
            } else {
                $summary['unchanged']++;
            }
        }

        foreach (array_chunk($inserts, 250) as $chunk) {
            DB::table('volume_noodles')->insert($chunk);
        }

        return $summary;
    }

    private function changed(object $current, array $values): bool
    {
        return collect($values)->contains(fn (mixed $value, string $field) => (float) $current->{$field} !== (float) $value);
    }

    private function volume(mixed $value, int $sourceRow, string $field): float
    {
        if (! is_numeric($value) || (float) $value < 0 || (float) $value > 9999999999999999.99) {
            throw new RuntimeException("VOLNDL.DBF baris {$sourceRow}: nilai {$field} tidak valid.");
        }

        return round((float) $value, 2);
    }
}
