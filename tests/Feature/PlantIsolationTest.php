<?php

use App\Models\Factory;
use App\Models\FactoryArea;
use App\Models\FinishedGood;
use App\Models\AreaNoodle;
use App\Models\Noodle;
use App\Models\NoodleFormula;
use App\Models\Plant;
use App\Models\RawMaterial;
use App\Models\Synonim;
use App\Models\User;
use App\Models\VolumeNoodle;
use App\Support\PlantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('plants are database masters and do not populate factory data', function () {
    expect(Plant::query()->orderBy('code')->pluck('code')->all())->toBe(['2872', '2873'])
        ->and(Factory::query()->count())->toBe(0);
});

test('raw materials and finished goods are isolated by active plant', function () {
    $ingredient = Plant::where('code', '2872')->firstOrFail();
    $packing = Plant::where('code', '2873')->firstOrFail();
    $context = app(PlantContext::class);

    $context->set($ingredient);
    $ingredientMaterial = RawMaterial::create([
        'code' => 'RM001', 'material_id' => 'MAT001', 'description' => 'Ingredient RM',
        'unit' => 'KG', 'currency_type' => 'Rp',
    ]);
    $ingredientGood = FinishedGood::create(['code' => 'FG001', 'description' => 'Ingredient FG']);

    $context->set($packing);
    $packingMaterial = RawMaterial::create([
        'code' => 'RM001', 'material_id' => 'MAT001', 'description' => 'Packing RM',
        'unit' => 'KG', 'currency_type' => 'Rp',
    ]);
    $packingGood = FinishedGood::create(['code' => 'FG001', 'description' => 'Packing FG']);

    expect(RawMaterial::pluck('id')->all())->toBe([$packingMaterial->id])
        ->and(FinishedGood::pluck('id')->all())->toBe([$packingGood->id])
        ->and($packingMaterial->plant_id)->toBe($packing->id)
        ->and($packingGood->plant_id)->toBe($packing->id);

    $context->set($ingredient);

    expect(RawMaterial::pluck('id')->all())->toBe([$ingredientMaterial->id])
        ->and(FinishedGood::pluck('id')->all())->toBe([$ingredientGood->id]);
});

test('login and header plant switch store the selected active plant', function () {
    $user = User::factory()->create([
        'username' => 'plant.admin',
        'password' => 'Password123',
        'role' => 'superadmin',
    ]);
    $packing = Plant::where('code', '2873')->firstOrFail();
    $ingredient = Plant::where('code', '2872')->firstOrFail();

    $this->post(route('login.store'), [
        'username' => 'plant.admin',
        'password' => 'Password123',
        'plant_id' => $packing->id,
    ])->assertRedirect(route('dashboard'))->assertSessionHas('active_plant_id', $packing->id);

    $this->assertAuthenticatedAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Plant aktif')
        ->assertSee('Pilih plant')
        ->assertSee('Data akan mengikuti plant yang dipilih.')
        ->assertSee('2873')
        ->assertSee('Blending &amp; Packing', false);

    $this->put(route('plant.active.update'), ['plant_id' => $ingredient->id])
        ->assertRedirect()
        ->assertSessionHas('active_plant_id', $ingredient->id);
});

test('route model binding cannot access a raw material from another plant', function () {
    $user = User::factory()->create(['role' => 'superadmin']);
    $ingredient = Plant::where('code', '2872')->firstOrFail();
    $packing = Plant::where('code', '2873')->firstOrFail();
    app(PlantContext::class)->set($ingredient);
    $material = RawMaterial::create([
        'code' => 'RM001', 'description' => 'Ingredient RM', 'unit' => 'KG', 'currency_type' => 'Rp',
    ]);

    $this->actingAs($user)
        ->withSession(['active_plant_id' => $packing->id])
        ->get(route('admin.maintenance.raw-material.edit', $material))
        ->assertNotFound();
});

test('a target raw material can map to a finished good owned by another plant', function () {
    $user = User::factory()->create(['role' => 'superadmin']);
    $ingredient = Plant::where('code', '2872')->firstOrFail();
    $packing = Plant::where('code', '2873')->firstOrFail();

    app(PlantContext::class)->set($ingredient);
    $sourceGood = FinishedGood::create(['code' => 'FG2872', 'description' => 'FG Ingredient']);

    app(PlantContext::class)->set($packing);
    $targetMaterial = RawMaterial::create([
        'code' => 'RM2873', 'description' => 'RM Blending', 'unit' => 'KG', 'currency_type' => 'Rp',
    ]);

    $this->actingAs($user)
        ->withSession(['active_plant_id' => $packing->id])
        ->post(route('admin.maintenance.synonim.store'), [
            'rm_code' => $targetMaterial->code,
            'fg_id' => $sourceGood->id,
        ])
        ->assertRedirect(route('admin.maintenance.synonim.index'));

    $mapping = Synonim::where('raw_material_id', $targetMaterial->id)->firstOrFail();

    expect($mapping->finished_good_id)->toBe($sourceGood->id)
        ->and($mapping->finishedGood->plant_id)->toBe($ingredient->id);
});

test('noodle area formula and volume data are isolated by active plant', function () {
    $ingredient = Plant::where('code', '2872')->firstOrFail();
    $packing = Plant::where('code', '2873')->firstOrFail();
    $context = app(PlantContext::class);

    $context->set($ingredient);
    $ingredientNoodle = Noodle::create(['code' => 'NDL001', 'description' => 'Ingredient Noodle', 'unit' => 'KG']);
    $ingredientArea = AreaNoodle::create(['code' => 'A01', 'description' => 'Ingredient Area']);
    $ingredientFormula = NoodleFormula::create(['noodle_id' => $ingredientNoodle->id]);
    $ingredientVolume = VolumeNoodle::create([
        'area_noodle_id' => $ingredientArea->id,
        'noodle_id' => $ingredientNoodle->id,
        'january' => 10,
    ]);

    $context->set($packing);
    $packingNoodle = Noodle::create(['code' => 'NDL001', 'description' => 'Packing Noodle', 'unit' => 'KG']);
    $packingArea = AreaNoodle::create(['code' => 'A01', 'description' => 'Packing Area']);
    $packingFormula = NoodleFormula::create(['noodle_id' => $packingNoodle->id]);
    $packingVolume = VolumeNoodle::create([
        'area_noodle_id' => $packingArea->id,
        'noodle_id' => $packingNoodle->id,
        'january' => 20,
    ]);

    expect(Noodle::pluck('id')->all())->toBe([$packingNoodle->id])
        ->and(AreaNoodle::pluck('id')->all())->toBe([$packingArea->id])
        ->and(NoodleFormula::pluck('id')->all())->toBe([$packingFormula->id])
        ->and(VolumeNoodle::pluck('id')->all())->toBe([$packingVolume->id]);

    $context->set($ingredient);

    expect(Noodle::pluck('id')->all())->toBe([$ingredientNoodle->id])
        ->and(AreaNoodle::pluck('id')->all())->toBe([$ingredientArea->id])
        ->and(NoodleFormula::pluck('id')->all())->toBe([$ingredientFormula->id])
        ->and(VolumeNoodle::pluck('id')->all())->toBe([$ingredientVolume->id]);
});

test('factory stays global while its area slots are separated per plant', function () {
    $ingredient = Plant::where('code', '2872')->firstOrFail();
    $packing = Plant::where('code', '2873')->firstOrFail();
    $context = app(PlantContext::class);
    $factory = Factory::create(['code' => 'F1', 'description' => 'Factory Global']);

    $context->set($ingredient);
    $ingredientArea = AreaNoodle::create(['code' => 'A01', 'description' => 'Ingredient Area']);
    $ingredientSlot = FactoryArea::create([
        'factory_id' => $factory->id,
        'area_noodle_id' => $ingredientArea->id,
        'position' => 1,
    ]);

    $context->set($packing);
    $packingArea = AreaNoodle::create(['code' => 'A01', 'description' => 'Packing Area']);
    $packingSlot = FactoryArea::create([
        'factory_id' => $factory->id,
        'area_noodle_id' => $packingArea->id,
        'position' => 1,
    ]);

    expect(Factory::find($factory->id))->not->toBeNull()
        ->and($factory->areaSlots()->pluck('id')->all())->toBe([$packingSlot->id]);

    $context->set($ingredient);

    expect(Factory::find($factory->id))->not->toBeNull()
        ->and($factory->areaSlots()->pluck('id')->all())->toBe([$ingredientSlot->id]);
});
