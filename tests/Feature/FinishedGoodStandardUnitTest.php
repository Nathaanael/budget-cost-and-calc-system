<?php

use App\Models\FinishedGood;
use App\Models\FinishedGoodFormulaItem;
use App\Models\RawMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('standard unit follows excel rounding for RM and packaging', function () {
    // RM (tanpa ID): =ROUND(H3/20000*1000;5)
    expect(FinishedGoodFormulaItem::calculateStandardUnit(3.123456, 20000, false))->toBe(0.15617)
        ->and(FinishedGoodFormulaItem::calculateStandardUnit(240, 245, false))->toBe(979.59184)
        // Packaging (punya ID): =ROUND(H4/20000;8)
        ->and(FinishedGoodFormulaItem::calculateStandardUnit(3.123456, 20000, true))->toBe(0.00015617)
        ->and(FinishedGoodFormulaItem::calculateStandardUnit(1, 3, true))->toBe(0.33333333)
        ->and(FinishedGoodFormulaItem::calculateStandardUnit(10, 0, true))->toBe(0.0);
});

test('finished good formula cannot be saved without batch', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $finishedGood = FinishedGood::create(['code' => '765433', 'description' => 'FG Tanpa Batch', 'batch' => 0, 'active' => 'Y']);
    $rawMaterial = RawMaterial::create([
        'code' => '654323',
        'description' => 'Raw Material Tiga',
        'unit' => 'Kg',
        'currency_type' => 'Rp',
        'type_rm' => 'LOCAL',
    ]);

    $this->actingAs($superadmin)
        ->putJson(route('admin.maintenance.formula.fg.update', $finishedGood), [
            'rows' => [['code' => $rawMaterial->code, 'standard_batch' => 10, 'deleted' => false]],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('rows');
});
