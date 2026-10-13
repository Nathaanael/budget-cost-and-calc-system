<?php

use App\Models\FinishedGood;
use App\Models\FinishedGoodFormula;
use App\Models\FinishedGoodFormulaItem;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** @return array{0: FinishedGood, 1: FinishedGood, 2: RawMaterial} */
function unitCostBatchFixture(): array
{
    $base = RawMaterial::create(['code' => 'RM-BASE', 'material_id' => 'MAT-BASE', 'description' => 'Base', 'unit' => 'KG', 'wastage_all' => 0.1, 'currency_type' => 'Rp', 'type_rm' => 'LOCAL']);
    foreach (RawMaterialPrice::PERIODS as $period) {
        RawMaterialPrice::create(['raw_material_id' => $base->id, 'period' => $period, 'rupiah_amount' => 100]);
    }

    $multi = FinishedGood::create(['code' => 'FG-MULTI', 'description' => 'Intermediate', 'multi_level' => 'Y', 'active' => 'Y', 'pe_cikampek' => 5, 'pe_semarang' => 7]);
    $multiFormula = FinishedGoodFormula::create(['finished_good_id' => $multi->id]);
    FinishedGoodFormulaItem::create(['finished_good_formula_id' => $multiFormula->id, 'raw_material_id' => $base->id, 'standard' => 2, 'position' => 1]);

    $intermediate = RawMaterial::create(['code' => 'FG-MULTI', 'material_id' => 'MAT-INTERMEDIATE', 'description' => 'Intermediate', 'unit' => 'KG', 'wastage_all' => 0, 'currency_type' => 'Rp', 'type_rm' => 'LOCAL']);
    $regular = FinishedGood::create(['code' => 'FG-FINAL', 'description' => 'Final', 'multi_level' => 'N', 'active' => 'Y', 'pe_cikampek' => 10, 'pe_palembang' => 20]);
    $regularFormula = FinishedGoodFormula::create(['finished_good_id' => $regular->id]);
    FinishedGoodFormulaItem::create(['finished_good_formula_id' => $regularFormula->id, 'raw_material_id' => $intermediate->id, 'standard' => 3, 'position' => 1]);

    return [$multi, $regular, $intermediate];
}

test('batched unit cost calculation matches the single request result', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    [$multi, $regular, $intermediate] = unitCostBatchFixture();
    FinishedGood::create(['code' => 'FG-NOFORMULA', 'description' => 'Tanpa Formula', 'multi_level' => 'N', 'active' => 'Y']);

    $plan = $this->actingAs($superadmin)
        ->postJson(route('admin.calculate.unit-cost-price.plan'), ['calculate_multi_level' => true, 'calculate_all' => true])
        ->assertOk()
        ->assertJsonPath('multi_level_ids', [$multi->id])
        ->assertJsonCount(2, 'regular_ids')
        ->json();

    $this->postJson(route('admin.calculate.unit-cost-price.batch'), ['stage' => 'multi_level', 'finished_good_ids' => $plan['multi_level_ids']])
        ->assertOk()
        ->assertJsonPath('summary.calculated_finished_goods', 1)
        ->assertJsonPath('summary.propagated_materials', 1);

    foreach (array_chunk($plan['regular_ids'], 1) as $chunk) {
        $this->postJson(route('admin.calculate.unit-cost-price.batch'), ['stage' => 'regular', 'finished_good_ids' => $chunk])->assertOk();
    }

    // Nilai sama dengan test kalkulasi satu request (Calc3 lama).
    expect((float) $multi->fresh()->unit_cost_current)->toBe(220.0)
        ->and((float) $multi->fresh()->unit_price_current)->toBe(225.0)
        ->and((float) $multi->fresh()->unit_price_semarang_current)->toBe(227.0)
        ->and((float) $regular->fresh()->unit_cost_current)->toBe(675.0)
        ->and((float) $regular->fresh()->unit_price_current)->toBe(685.0)
        ->and((float) $regular->fresh()->unit_price_palembang_current)->toBe(695.0);

    $this->assertDatabaseHas('raw_material_prices', [
        'raw_material_id' => $intermediate->id, 'period' => 'current', 'rupiah_amount' => 225, 'source_kind' => 'multi_level',
    ]);
});

test('unit cost plan respects selection and the multi level option', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    [$multi, $regular] = unitCostBatchFixture();

    $this->actingAs($superadmin)
        ->postJson(route('admin.calculate.unit-cost-price.plan'), ['calculate_multi_level' => false, 'calculate_all' => true])
        ->assertOk()
        ->assertJsonPath('multi_level_ids', [])
        ->assertJsonPath('regular_ids', [$regular->id]);

    $this->postJson(route('admin.calculate.unit-cost-price.plan'), ['calculate_multi_level' => true, 'finished_good_ids' => [$multi->id]])
        ->assertOk()
        ->assertJsonPath('multi_level_ids', [$multi->id])
        ->assertJsonPath('regular_ids', []);
});

test('unit cost batch rejects finished goods from the wrong stage and non superadmin', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $user = User::factory()->create(['role' => 'user']);
    [$multi, $regular] = unitCostBatchFixture();

    $this->actingAs($superadmin)
        ->postJson(route('admin.calculate.unit-cost-price.batch'), ['stage' => 'regular', 'finished_good_ids' => [$multi->id]])
        ->assertStatus(422);
    $this->postJson(route('admin.calculate.unit-cost-price.batch'), ['stage' => 'multi_level', 'finished_good_ids' => [$regular->id]])
        ->assertStatus(422);

    $this->actingAs($user)
        ->postJson(route('admin.calculate.unit-cost-price.batch'), ['stage' => 'regular', 'finished_good_ids' => [$regular->id]])
        ->assertForbidden();
});
