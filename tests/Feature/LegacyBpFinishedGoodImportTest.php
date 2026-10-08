<?php

use App\Models\FinishedGood;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('BP finished good DBF import supports dry-run and repeatable upsert', function () {
    $plant = Plant::where('code', '2873')->firstOrFail();

    expect(Artisan::call('legacy:import-bp-finished-good', ['--dry-run' => true]))->toBe(0)
        ->and(FinishedGood::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(0);

    expect(Artisan::call('legacy:import-bp-finished-good'))->toBe(0);

    $count = FinishedGood::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count();
    $finishedGood = FinishedGood::withoutGlobalScope('plant')
        ->where('plant_id', $plant->id)
        ->where('code', '13511')
        ->firstOrFail();

    expect($count)->toBe(1673)
        ->and($finishedGood->description)->toBe('INGREDIENT M208600/BUBUK GULA')
        ->and($finishedGood->active)->toBe('N')
        ->and($finishedGood->unit_cost_current)->toBe('9725.37')
        ->and($finishedGood->unit_price_semarang_current)->toBe('9725.37');

    expect(Artisan::call('legacy:import-bp-finished-good'))->toBe(0)
        ->and(FinishedGood::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe($count);
});
