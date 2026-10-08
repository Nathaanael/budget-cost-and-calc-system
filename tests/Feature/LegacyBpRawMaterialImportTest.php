<?php

use App\Models\Plant;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('BP raw material DBF import supports prices duplicate legacy ids and repeatable upsert', function () {
    $plant = Plant::where('code', '2873')->firstOrFail();

    expect(Artisan::call('legacy:import-bp-raw-material', ['--dry-run' => true]))->toBe(0)
        ->and(RawMaterial::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe(0);

    expect(Artisan::call('legacy:import-bp-raw-material'))->toBe(0);

    $count = RawMaterial::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count();
    $rawMaterial = RawMaterial::withoutGlobalScope('plant')
        ->where('plant_id', $plant->id)
        ->where('code', '101550')
        ->firstOrFail();

    expect($count)->toBe(1329)
        ->and($rawMaterial->description)->toBe('SAUCE TERIYAKI/TSJ 25KG')
        ->and($rawMaterial->currency_type)->toBe('Rp')
        ->and($rawMaterial->material_id)->toBeNull()
        ->and(RawMaterialPrice::where('raw_material_id', $rawMaterial->id)->count())->toBe(6)
        ->and(RawMaterialPrice::where('raw_material_id', $rawMaterial->id)->where('period', 'current')->firstOrFail()->rupiah_amount)->toBe(18530.0);

    expect(Artisan::call('legacy:import-bp-raw-material'))->toBe(0)
        ->and(RawMaterial::withoutGlobalScope('plant')->where('plant_id', $plant->id)->count())->toBe($count)
        ->and(RawMaterialPrice::count())->toBe($count * 6);
});
