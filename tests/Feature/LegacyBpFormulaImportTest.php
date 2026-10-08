<?php

use App\Models\FinishedGood;
use App\Models\FinishedGoodFormula;
use App\Models\FinishedGoodFormulaItem;
use App\Models\Noodle;
use App\Models\NoodleFormula;
use App\Models\NoodleFormulaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('BP formula import migrates noodle and finished good formulas repeatably', function () {
    expect(Artisan::call('legacy:import-bp-noodle'))->toBe(0)
        ->and(Artisan::call('legacy:import-bp-finished-good'))->toBe(0)
        ->and(Artisan::call('legacy:import-bp-raw-material'))->toBe(0)
        ->and(Artisan::call('legacy:import-bp-formulas', ['--dry-run' => true]))->toBe(0)
        ->and(NoodleFormula::withoutGlobalScope('plant')->count())->toBe(0)
        ->and(FinishedGoodFormula::count())->toBe(0);

    expect(Artisan::call('legacy:import-bp-formulas'))->toBe(0)
        ->and(NoodleFormula::withoutGlobalScope('plant')->count())->toBe(862)
        ->and(NoodleFormulaItem::count())->toBe(1745)
        ->and(FinishedGoodFormula::count())->toBe(1667)
        ->and(FinishedGoodFormulaItem::count())->toBe(19007);

    $noodle = Noodle::withoutGlobalScope('plant')->where('code', '200940I')->firstOrFail();
    $noodleFormula = NoodleFormula::withoutGlobalScope('plant')->where('noodle_id', $noodle->id)->firstOrFail();
    $finishedGood = FinishedGood::withoutGlobalScope('plant')->where('code', '13511')->firstOrFail();
    $finishedGoodFormula = FinishedGoodFormula::where('finished_good_id', $finishedGood->id)->firstOrFail();

    expect($noodleFormula->items()->count())->toBe(2)
        ->and($noodleFormula->items()->firstOrFail()->standard)->toBe('1.000000')
        ->and($finishedGoodFormula->items()->count())->toBe(4)
        ->and($finishedGoodFormula->items()->orderBy('position')->firstOrFail()->standard)->toBe('0.050000');

    expect(Artisan::call('legacy:import-bp-formulas'))->toBe(0)
        ->and(NoodleFormulaItem::count())->toBe(1745)
        ->and(FinishedGoodFormulaItem::count())->toBe(19007);
});
