<?php

use App\Models\Plant;
use App\Models\VolumeNoodle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('BP volume noodle import supports dry-run totals and repeatable upsert', function () {
    $plant = Plant::where('code', '2873')->firstOrFail();

    expect(Artisan::call('legacy:import-bp-area'))->toBe(0)
        ->and(Artisan::call('legacy:import-bp-noodle'))->toBe(0)
        ->and(Artisan::call('legacy:import-bp-volume-noodle', ['--dry-run' => true]))->toBe(0)
        ->and(VolumeNoodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(0);

    expect(Artisan::call('legacy:import-bp-volume-noodle'))->toBe(0);

    $count = VolumeNoodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count();
    $volume = VolumeNoodle::withoutGlobalScope('plant')
        ->where('plant_id', $plant->id)
        ->whereHas('areaNoodle', fn ($query) => $query->withoutGlobalScope('plant')->where('code', 'C1'))
        ->whereHas('noodle', fn ($query) => $query->withoutGlobalScope('plant')->where('code', '201010I'))
        ->firstOrFail();
    $sameNoodleAtW4 = VolumeNoodle::withoutGlobalScope('plant')
        ->where('plant_id', $plant->id)
        ->whereHas('areaNoodle', fn ($query) => $query->withoutGlobalScope('plant')->where('code', 'W4'))
        ->whereHas('noodle', fn ($query) => $query->withoutGlobalScope('plant')->where('code', '201010I'))
        ->firstOrFail();

    expect($count)->toBe(869)
        ->and($volume->january)->toBe(95.0)
        ->and($volume->december)->toBe(97.0)
        ->and($volume->total_le)->toBe(0.0)
        ->and($volume->total_aop)->toBe(1106.0)
        ->and($sameNoodleAtW4->january)->toBe(9.0)
        ->and($sameNoodleAtW4->total_aop)->toBe(111.0);

    expect(Artisan::call('legacy:import-bp-volume-noodle'))->toBe(0)
        ->and(VolumeNoodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe($count);
});
