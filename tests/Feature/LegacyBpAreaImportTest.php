<?php

use App\Models\AreaNoodle;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('BP area DBF import supports dry-run and repeatable upsert', function () {
    $plant = Plant::where('code', '2873')->firstOrFail();

    expect(Artisan::call('legacy:import-bp-area', ['--dry-run' => true]))->toBe(0)
        ->and(AreaNoodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(0);

    expect(Artisan::call('legacy:import-bp-area'))->toBe(0)
        ->and(AreaNoodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(15)
        ->and(AreaNoodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->where('code', 'E3')->value('description'))->toBe('UJUNG PANDANG');

    expect(Artisan::call('legacy:import-bp-area'))->toBe(0)
        ->and(AreaNoodle::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(15);
});
