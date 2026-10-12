<?php

use App\Models\FinishedGood;
use App\Models\FinishedGoodFormula;
use App\Models\FinishedGoodFormulaItem;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unit cost only calculates selected FG and rejects invalid selections', function () {
    $user = User::factory()->create(['role' => 'superadmin']);
    $rm = RawMaterial::create(['code' => 'BASE', 'material_id' => 'BASE', 'description' => 'Base', 'unit' => 'KG', 'currency_type' => 'Rp', 'type_rm' => 'LOCAL']);
    foreach (RawMaterialPrice::PERIODS as $period) {
        RawMaterialPrice::create(['raw_material_id' => $rm->id, 'period' => $period, 'rupiah_amount' => 100]);
    }
    $goods = collect(['A', 'B', 'C'])->map(function ($code) use ($rm) {
        $fg = FinishedGood::create(['code' => $code, 'description' => $code, 'multi_level' => $code === 'C' ? 'Y' : 'N', 'unit_cost_le' => 999, 'unit_price_le' => 1000, 'pe_cikampek' => 5]);
        $formula = FinishedGoodFormula::create(['finished_good_id' => $fg->id]);
        FinishedGoodFormulaItem::create(['finished_good_formula_id' => $formula->id, 'raw_material_id' => $rm->id, 'standard' => 2, 'position' => 1]);

        return $fg;
    });
    $intermediate = RawMaterial::create(['code' => 'C', 'material_id' => 'C', 'description' => 'Intermediate', 'unit' => 'KG', 'currency_type' => 'Rp', 'type_rm' => 'LOCAL']);
    RawMaterialPrice::create(['raw_material_id' => $intermediate->id, 'period' => 'le', 'rupiah_amount' => 42]);
    $this->actingAs($user);
    foreach ([[], ['finished_good_ids' => []], ['finished_good_ids' => [999999]], ['finished_good_ids' => [$goods[0]->id, $goods[0]->id]], ['finished_good_ids' => [$goods[0]->id, 999999]]] as $selection) {
        $this->postJson(route('admin.calculate.unit-cost-price.store'), ['calculate_multi_level' => true, ...$selection])->assertUnprocessable();
    }
    $this->postJson(route('admin.calculate.unit-cost-price.store'), ['calculate_multi_level' => false, 'finished_good_ids' => [$goods[2]->id]])->assertUnprocessable();
    $this->postJson(route('admin.calculate.unit-cost-price.store'), ['calculate_multi_level' => true, 'finished_good_ids' => [$goods[0]->id]])
        ->assertOk()->assertJsonPath('summary.calculated_finished_goods', 1)->assertJsonPath('summary.updated_periods', 6)->assertJsonPath('summary.propagated_materials', 0);
    expect((float) $goods[0]->fresh()->unit_cost_le)->toBe(200.0)
        ->and((float) $goods[0]->fresh()->unit_price_le)->toBe(205.0)
        ->and((float) $goods[1]->fresh()->unit_cost_le)->toBe(999.0)
        ->and((float) $goods[2]->fresh()->unit_cost_le)->toBe(999.0);
    expect($intermediate->prices()->first()->rupiah_amount)->toBe(42.0);

    $this->postJson(route('admin.calculate.unit-cost-price.store'), [
        'calculate_multi_level' => false,
        'calculate_all' => true,
    ])->assertOk()
        ->assertJsonPath('summary.calculated_finished_goods', 2)
        ->assertJsonPath('summary.updated_periods', 12);

    expect((float) $goods[1]->fresh()->unit_cost_le)->toBe(200.0)
        ->and((float) $goods[2]->fresh()->unit_cost_le)->toBe(999.0);
});

test('selected multi level FG without formula does not propagate stale prices', function () {
    $user = User::factory()->create(['role' => 'superadmin']);
    $fg = FinishedGood::create(['code' => 'C', 'description' => 'Intermediate', 'multi_level' => 'Y', 'unit_price_le' => 999]);
    $rm = RawMaterial::create(['code' => 'C', 'material_id' => 'C', 'description' => 'Intermediate', 'unit' => 'KG', 'currency_type' => 'Rp', 'type_rm' => 'LOCAL']);
    RawMaterialPrice::create(['raw_material_id' => $rm->id, 'period' => 'le', 'rupiah_amount' => 42]);
    $this->actingAs($user)->postJson(route('admin.calculate.unit-cost-price.store'), ['calculate_multi_level' => true, 'finished_good_ids' => [$fg->id]])
        ->assertOk()->assertJsonPath('summary.calculated_finished_goods', 0)->assertJsonPath('summary.skipped_finished_goods', 1)->assertJsonPath('summary.propagated_materials', 0);
    expect($rm->prices()->first()->rupiah_amount)->toBe(42.0)->and($rm->prices()->count())->toBe(1);
});
