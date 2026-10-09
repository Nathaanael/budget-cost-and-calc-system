<?php

use App\Models\FinishedGood;
use App\Models\Plant;
use App\Models\RawMaterial;
use App\Models\Synonim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('BP synonim import keeps source finished goods in the BP plant', function () {
    $packingPlant = Plant::where('code', '2873')->firstOrFail();

    expect(Artisan::call('legacy:import-bp-raw-material'))->toBe(0)
        ->and(Artisan::call('legacy:import-bp-synonim', ['--dry-run' => true]))->toBe(0)
        ->and(FinishedGood::withoutGlobalScope('plant')->where('plant_id', $packingPlant->id)->count())->toBe(0)
        ->and(Synonim::count())->toBe(0);

    expect(Artisan::call('legacy:import-bp-synonim'))->toBe(0);

    $rawMaterial = RawMaterial::withoutGlobalScope('plant')
        ->where('plant_id', $packingPlant->id)
        ->where('code', '200761')
        ->firstOrFail();
    $finishedGood = FinishedGood::withoutGlobalScope('plant')
        ->where('plant_id', $packingPlant->id)
        ->where('code', '200761')
        ->firstOrFail();
    $synonim = Synonim::where('raw_material_id', $rawMaterial->id)->firstOrFail();

    expect(FinishedGood::withoutGlobalScope('plant')->where('plant_id', $packingPlant->id)->count())->toBe(749)
        ->and(Synonim::count())->toBe(749)
        ->and($finishedGood->description)->toBe('BUBUK AB')
        ->and($synonim->finished_good_id)->toBe($finishedGood->id)
        ->and($rawMaterial->plant_id)->toBe($packingPlant->id)
        ->and($finishedGood->plant_id)->toBe($packingPlant->id);

    expect(Artisan::call('legacy:import-bp-synonim'))->toBe(0)
        ->and(FinishedGood::withoutGlobalScope('plant')->where('plant_id', $packingPlant->id)->count())->toBe(749)
        ->and(Synonim::count())->toBe(749);
});
