<?php

use App\Models\Noodle;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('BP noodle DBF import supports dry-run and repeatable upsert', function () {
    $plant = Plant::where('code', '2873')->firstOrFail();

    expect(Artisan::call('legacy:import-bp-noodle', ['--dry-run' => true]))->toBe(0)
        ->and(Noodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(0);

    expect(Artisan::call('legacy:import-bp-noodle'))->toBe(0);

    $count = Noodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count();

    expect($count)->toBeGreaterThan(0)
        ->and(Noodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->where('code', '200939I')->exists())->toBeTrue();

    expect(Artisan::call('legacy:import-bp-noodle'))->toBe(0)
        ->and(Noodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe($count);
});
