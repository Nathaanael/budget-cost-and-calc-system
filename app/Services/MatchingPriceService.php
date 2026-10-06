<?php

namespace App\Services;

use App\Models\FinishedGood;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use App\Models\Synonim;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MatchingPriceService
{
    public const PERIODS = ['le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'];

    public const FACTORIES = [
        'cikampek' => 'unit_price',
        'semarang' => 'unit_price_semarang',
        'surabaya' => 'unit_price_surabaya',
        'palembang' => 'unit_price_palembang',
    ];

    public function match(string $factory, User $user, array $materialIds): int
    {
        if (! array_key_exists($factory, self::FACTORIES)) {
            throw ValidationException::withMessages(['factory' => __('Invalid source factory.')]);
        }

        if ($materialIds === []) {
            throw ValidationException::withMessages(['material_ids' => __('Select at least one material to match.')]);
        }

        return DB::transaction(function () use ($factory, $user, $materialIds): int {
            $mappings = Synonim::whereIn('raw_material_id', $materialIds)->orderBy('raw_material_id')->lockForUpdate()->get();
            if ($mappings->count() !== count($materialIds)) {
                throw ValidationException::withMessages(['material_ids' => __('The selected mappings have changed. Reload and select the materials again.')]);
            }
            $materials = RawMaterial::whereIn('id', $mappings->pluck('raw_material_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $goods = FinishedGood::whereIn('id', $mappings->pluck('finished_good_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $prices = RawMaterialPrice::whereIn('raw_material_id', $materials->keys())
                ->whereIn('period', self::PERIODS)->orderBy('id')->lockForUpdate()->get()
                ->keyBy(fn ($price) => $price->raw_material_id.':'.$price->period);
            $batch = (string) Str::uuid();
            $now = now();

            foreach ($mappings as $mapping) {
                $material = $materials->get($mapping->raw_material_id);
                $good = $goods->get($mapping->finished_good_id);
                if (! $material || ! $good) {
                    throw ValidationException::withMessages(['matching' => __('A Synonim source or target is missing. No prices were saved.')]);
                }

                foreach (self::PERIODS as $period) {
                    $amount = $good->{self::FACTORIES[$factory].'_'.$period};
                    if ($amount === null) {
                        throw ValidationException::withMessages(['matching' => __('A source price is missing. No prices were saved.')]);
                    }
                    $price = $prices->get($material->id.':'.$period);
                    $before = $price?->getRawOriginal('rupiah_amount');
                    $price ??= new RawMaterialPrice([
                        'raw_material_id' => $material->id, 'period' => $period,
                        'usd_amount' => 0, 'created_by' => $user->id,
                    ]);
                    // Calc2 copies even zero/unchanged values. It never touches Current or USD.
                    $price->fill([
                        'rupiah_amount' => $amount, 'source_kind' => 'matching',
                        'reference_id' => null, 'exchange_rate' => null,
                        'calculated_at' => $now, 'updated_by' => $user->id,
                    ])->save();
                    DB::table('matching_price_histories')->insert([
                        'batch_id' => $batch, 'rm_code' => $material->code, 'fg_code' => $good->code,
                        'factory' => $factory, 'period' => $period,
                        'price_before' => $before, 'price_after' => $amount,
                        'user_id' => $user->id, 'user_name' => $user->name ?? $user->username,
                        'matched_at' => $now,
                    ]);
                }
            }

            return $mappings->count();
        });
    }
}
