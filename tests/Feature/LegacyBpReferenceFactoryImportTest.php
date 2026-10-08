<?php

use App\Models\Factory;
use App\Models\FactoryArea;
use App\Models\Plant;
use App\Models\Reference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('BP reference and factory import preserves global factories and plant area relations', function () {
    $plant = Plant::where('code', '2873')->firstOrFail();

    expect(Artisan::call('legacy:import-bp-area'))->toBe(0)
        ->and(Artisan::call('legacy:import-bp-reference-factory', ['--dry-run' => true]))->toBe(0)
        ->and(Reference::count())->toBe(0)
        ->and(Factory::count())->toBe(0);

    expect(Artisan::call('legacy:import-bp-reference-factory'))->toBe(0)
        ->and(Reference::count())->toBe(1)
        ->and(Factory::count())->toBe(25)
        ->and(FactoryArea::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(30);

    $reference = Reference::where('code', '00')->firstOrFail();
    $factory = Factory::where('code', 'S1')->firstOrFail();

    expect($reference->period_description)->toBe('AGUSTUS 2017')
        ->and($reference->rate_current)->toBe('13300.00')
        ->and($factory->description)->toBe('SEASONING/PURWAKARTA')
        ->and($factory->areaSlots()->withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(5);

    expect(Artisan::call('legacy:import-bp-reference-factory'))->toBe(0)
        ->and(Reference::count())->toBe(1)
        ->and(Factory::count())->toBe(25)
        ->and(FactoryArea::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(30);
});
