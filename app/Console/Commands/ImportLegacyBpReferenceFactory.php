<?php

namespace App\Console\Commands;

use App\Models\Plant;
use App\Support\LegacyDbfReader;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ImportLegacyBpReferenceFactory extends Command
{
    protected $signature = 'legacy:import-bp-reference-factory
        {--reference-file=prg/COSTREF.DBF : Lokasi file COSTREF.DBF}
        {--factory-file=prg/FACTORY.DBF : Lokasi file FACTORY.DBF}
        {--plant=2873 : Kode Plant untuk relasi Factory-Area}
        {--dry-run : Validasi data tanpa menyimpan}';

    protected $description = 'Impor Reference dan Factory BP beserta relasi Area Plant tujuan';

    public function handle(LegacyDbfReader $reader): int
    {
        try {
            $referencePath = $this->resolvePath((string) $this->option('reference-file'));
            $factoryPath = $this->resolvePath((string) $this->option('factory-file'));
            $plant = Plant::query()->where('code', (string) $this->option('plant'))->firstOrFail();
            $references = $this->readReferences($reader, $referencePath);
            [$factories, $missingAreas] = $this->readFactories($reader, $factoryPath, (int) $plant->id);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $areaCount = $factories->sum(fn (array $factory) => count($factory['areas']));

        $this->info("Reference: {$referencePath}");
        $this->info("Factory: {$factoryPath}");
        $this->info("Relasi Area tujuan: Plant {$plant->code} - {$plant->description}");
        $this->table(
            ['Reference', 'Factory Global', 'Relasi Area Siap', 'Relasi Area Dilewati'],
            [[$references->count(), $factories->count(), $areaCount, array_sum($missingAreas)]],
        );
        $this->table(
            ['Code', 'Description', 'Area tersedia'],
            $factories->map(fn (array $factory) => [
                $factory['code'],
                $factory['description'],
                collect($factory['areas'])->pluck('code')->implode(', '),
            ]),
        );

        if ($missingAreas !== []) {
            $this->warn('Beberapa kode area Factory dilewati karena master Area Noodle tidak tersedia pada Plant tujuan.');
            $this->table(
                ['Kode Area', 'Jumlah Referensi'],
                collect($missingAreas)->map(fn (int $count, string $code) => [$code, $count])->values(),
            );
        }

        if ($this->option('dry-run')) {
            $this->components->info('Dry-run Reference dan Factory BP berhasil. Database tidak diubah.');

            return self::SUCCESS;
        }

        $summary = DB::transaction(fn (): array => [
            'references' => $this->persistReferences($references),
            'factories' => $this->persistFactories($factories, (int) $plant->id),
        ]);

        $this->components->info('Impor Reference dan Factory BP berhasil.');
        $this->table(
            ['Jenis', 'Inserted', 'Updated', 'Unchanged', 'Relasi Area'],
            [
                ['Reference', ...array_values($summary['references']), '-'],
                ['Factory', ...array_values($summary['factories'])],
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

    /** @return Collection<int, array<string, mixed>> */
    private function readReferences(LegacyDbfReader $reader, string $path): Collection
    {
        $rows = collect();
        $seenCodes = [];

        foreach ($reader->records($path) as $sourceRow => $record) {
            $data = [
                'code' => strtoupper(trim((string) ($record['CODE'] ?? ''))),
                'description_1' => trim((string) ($record['DESC1'] ?? '')),
                'description_2' => trim((string) ($record['DESC2'] ?? '')),
                'period' => trim((string) ($record['PER'] ?? '')),
                'period_description' => trim((string) ($record['PERIOD'] ?? '')),
                'rate_current' => $this->number($record['RATE0'] ?? null),
                'rate_le' => $this->number($record['RATELE'] ?? null),
                'rate_1' => $this->number($record['RATE1'] ?? null),
                'rate_2' => $this->number($record['RATE2'] ?? null),
                'rate_3' => $this->number($record['RATE3'] ?? null),
                'rate_4' => $this->number($record['RATE4'] ?? null),
                'pe_ckp_current' => $this->number($record['PE00'] ?? null),
                'pe_ckp_le' => $this->number($record['PELE'] ?? null),
                'pe_ckp_1' => $this->number($record['PE01'] ?? null),
                'pe_ckp_2' => $this->number($record['PE02'] ?? null),
                'pe_ckp_3' => $this->number($record['PE03'] ?? null),
                'pe_ckp_4' => $this->number($record['PE04'] ?? null),
                'pe_smg_current' => $this->number($record['PE10'] ?? null),
                'pe_smg_le' => $this->number($record['PELE1'] ?? null),
                'pe_smg_1' => $this->number($record['PE11'] ?? null),
                'pe_smg_2' => $this->number($record['PE12'] ?? null),
                'pe_smg_3' => $this->number($record['PE13'] ?? null),
                'pe_smg_4' => $this->number($record['PE14'] ?? null),
                'pe_sby_current' => $this->number($record['PE20'] ?? null),
                'pe_sby_le' => $this->number($record['PELE2'] ?? null),
                'pe_sby_1' => $this->number($record['PE21'] ?? null),
                'pe_sby_2' => $this->number($record['PE22'] ?? null),
                'pe_sby_3' => $this->number($record['PE23'] ?? null),
                'pe_sby_4' => $this->number($record['PE24'] ?? null),
            ];
            $validator = Validator::make($data, [
                'code' => ['required', 'string', 'max:2'],
                'description_1' => ['required', 'string', 'max:30'],
                'description_2' => ['required', 'string', 'max:30'],
                'period' => ['required', 'string', 'max:8'],
                'period_description' => ['required', 'string', 'max:15'],
                'rate_*' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
                'pe_*' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            ]);

            if ($validator->fails()) {
                throw new RuntimeException("COSTREF.DBF baris {$sourceRow}: ".$validator->errors()->first());
            }

            if (isset($seenCodes[$data['code']])) {
                throw new RuntimeException("COSTREF.DBF baris {$sourceRow}: code {$data['code']} duplikat.");
            }

            $seenCodes[$data['code']] = true;
            $rows->push($data);
        }

        return $rows;
    }

    /**
     * @return array{0: Collection<int, array<string, mixed>>, 1: array<string, int>}
     */
    private function readFactories(LegacyDbfReader $reader, string $path, int $plantId): array
    {
        $rows = collect();
        $missingAreas = [];
        $seenCodes = [];
        $areaCatalog = DB::table('area_noodles')->where('plant_id', $plantId)->pluck('id', 'code');

        foreach ($reader->records($path) as $sourceRow => $record) {
            $code = strtoupper(trim((string) ($record['CODE'] ?? '')));
            $description = trim((string) ($record['DESC'] ?? ''));
            $validator = Validator::make(compact('code', 'description'), [
                'code' => ['required', 'string', 'max:2'],
                'description' => ['required', 'string', 'max:20'],
            ]);

            if ($validator->fails()) {
                throw new RuntimeException("FACTORY.DBF baris {$sourceRow}: ".$validator->errors()->first());
            }

            if (isset($seenCodes[$code])) {
                throw new RuntimeException("FACTORY.DBF baris {$sourceRow}: code {$code} duplikat.");
            }

            $seenCodes[$code] = true;
            $areas = [];

            foreach ($this->areaCodes((string) ($record['AREA'] ?? '')) as $position => $areaCode) {
                $areaId = $areaCatalog->get($areaCode);

                if (! $areaId) {
                    $missingAreas[$areaCode] = ($missingAreas[$areaCode] ?? 0) + 1;

                    continue;
                }

                $areas[] = [
                    'code' => $areaCode,
                    'area_noodle_id' => (int) $areaId,
                    'position' => $position,
                ];
            }

            $rows->push(compact('code', 'description', 'areas'));
        }

        ksort($missingAreas);

        return [$rows, $missingAreas];
    }

    /** @return array<int, string> */
    private function areaCodes(string $value): array
    {
        $areas = [];

        foreach (range(0, 9) as $index) {
            $code = strtoupper(trim(substr($value, $index * 3, 2)));

            if ($code !== '') {
                $areas[$index + 1] = $code;
            }
        }

        return $areas;
    }

    /** @return array{inserted: int, updated: int, unchanged: int} */
    private function persistReferences(Collection $references): array
    {
        $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0];
        $existing = DB::table('references')->whereIn('code', $references->pluck('code'))->get()->keyBy('code');
        $usedIds = DB::table('references')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $now = now();

        foreach ($references as $data) {
            $current = $existing->get($data['code']);

            if (! $current) {
                DB::table('references')->insert([
                    'id' => $this->reserveId($usedIds),
                    ...$data,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $summary['inserted']++;
            } elseif ($this->changed($current, $data)) {
                DB::table('references')->where('id', $current->id)->update([
                    ...$data,
                    'updated_by' => null,
                    'updated_at' => $now,
                ]);
                $summary['updated']++;
            } else {
                $summary['unchanged']++;
            }
        }

        return $summary;
    }

    /** @return array{inserted: int, updated: int, unchanged: int, areas: int} */
    private function persistFactories(Collection $factories, int $plantId): array
    {
        $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'areas' => 0];
        $existing = DB::table('factories')->whereIn('code', $factories->pluck('code'))->get()->keyBy('code');
        $usedFactoryIds = DB::table('factories')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $usedAreaIds = DB::table('factory_areas')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $now = now();

        foreach ($factories as $data) {
            $current = $existing->get($data['code']);
            $factoryId = $current ? (int) $current->id : $this->reserveId($usedFactoryIds);
            $existingAreas = $current
                ? DB::table('factory_areas')->where('plant_id', $plantId)->where('factory_id', $factoryId)
                    ->orderBy('position')->get(['area_noodle_id', 'position'])
                : collect();
            $areaChanged = ! $this->sameAreas($existingAreas, collect($data['areas']));
            $descriptionChanged = $current && $current->description !== $data['description'];

            if (! $current) {
                DB::table('factories')->insert([
                    'id' => $factoryId,
                    'code' => $data['code'],
                    'description' => $data['description'],
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $summary['inserted']++;
            } elseif ($descriptionChanged || $areaChanged) {
                if ($descriptionChanged) {
                    DB::table('factories')->where('id', $factoryId)->update([
                        'description' => $data['description'],
                        'updated_by' => null,
                        'updated_at' => $now,
                    ]);
                }
                $summary['updated']++;
            } else {
                $summary['unchanged']++;
            }

            if (! $current || $areaChanged) {
                DB::table('factory_areas')->where('plant_id', $plantId)->where('factory_id', $factoryId)->delete();

                foreach ($data['areas'] as $area) {
                    DB::table('factory_areas')->insert([
                        'id' => $this->reserveId($usedAreaIds),
                        'plant_id' => $plantId,
                        'factory_id' => $factoryId,
                        'area_noodle_id' => $area['area_noodle_id'],
                        'position' => $area['position'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $summary['areas'] += count($data['areas']);
        }

        return $summary;
    }

    private function changed(object $current, array $data): bool
    {
        return collect($data)->contains(fn (mixed $value, string $field) => $current->{$field} != $value);
    }

    private function sameAreas(Collection $existing, Collection $source): bool
    {
        if ($existing->count() !== $source->count()) {
            return false;
        }

        return $source->values()->every(function (array $area, int $index) use ($existing): bool {
            $current = $existing->values()->get($index);

            return $current
                && (int) $current->area_noodle_id === $area['area_noodle_id']
                && (int) $current->position === $area['position'];
        });
    }

    private function number(mixed $value): float
    {
        return $value === null || $value === '' ? 0.0 : (float) $value;
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

        throw new RuntimeException('Tidak dapat menghasilkan ID unik 5 digit.');
    }
}
