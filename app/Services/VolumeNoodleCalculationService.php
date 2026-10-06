<?php

namespace App\Services;

use App\Models\VolumeNoodle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VolumeNoodleCalculationService
{
    public function calculate(int $userId, int $areaId): int
    {
        return DB::transaction(function () use ($userId, $areaId): int {
            // Serialize rebuilds, including when no previous results exist.
            DB::table('volume_calculation_state')->where('id', 1)->lockForUpdate()->firstOrFail();
            if (! DB::table('area_noodles')->where('id', $areaId)->lockForUpdate()->first()) {
                throw ValidationException::withMessages(['area_id' => __('Select a valid area before calculating.')]);
            }

            $groupColumns = [
                'v.area_noodle_id', 'i.finished_good_id', 'a.code', 'fg.code',
                'fg.description', 'fg.product_type_1', 'fg.product_type_2',
            ];
            $query = DB::table('volume_noodles as v')
                ->where('v.area_noodle_id', $areaId)
                ->join('area_noodles as a', 'a.id', '=', 'v.area_noodle_id')
                ->join('noodles as n', 'n.id', '=', 'v.noodle_id')
                ->join('noodle_formulas as f', 'f.noodle_id', '=', 'v.noodle_id')
                ->join('noodle_formula_items as i', 'i.noodle_formula_id', '=', 'f.id')
                ->join('finished_goods as fg', 'fg.id', '=', 'i.finished_good_id')
                ->whereNull('f.deleted_at')
                ->whereNull('i.deleted_at')
                ->select([
                    'v.area_noodle_id', 'i.finished_good_id', 'a.code as area_code',
                    'fg.code as fg_code', 'fg.description', 'fg.product_type_1', 'fg.product_type_2',
                ])
                ->groupBy($groupColumns);

            foreach ([...VolumeNoodle::MONTH_FIELDS, ...VolumeNoodle::LE_FIELDS] as $field) {
                $query->selectRaw("SUM(v.{$field} * i.standard) as {$field}");
            }
            // Sum the source months, rather than trusting previously stored input totals.
            $annual = implode(' + ', array_map(fn ($field) => "v.{$field}", VolumeNoodle::MONTH_FIELDS));
            $le = implode(' + ', array_map(fn ($field) => "v.{$field}", VolumeNoodle::LE_FIELDS));
            $query->selectRaw("SUM(({$annual}) * i.standard) as total_aop")
                ->selectRaw("SUM(({$le}) * i.standard) as total_le")
                ->havingRaw("SUM(({$annual}) * i.standard) > 0 OR SUM(({$le}) * i.standard) > 0");

            $results = $query->get();
            // DELETE is transactional; TRUNCATE would break rollback on MySQL.
            DB::table('finished_good_volumes')->where('area_noodle_id', $areaId)->delete();
            foreach ($results->chunk(25) as $chunk) {
                DB::table('finished_good_volumes')->insert($chunk->map(fn ($row) => (array) $row)->all());
            }
            DB::table('volume_calculation_state')->where('id', 1)->update([
                'calculated_at' => now(), 'calculated_by' => $userId,
            ]);

            return $results->count();
        }, 3);
    }
}
