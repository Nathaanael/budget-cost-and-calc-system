<?php

use App\Models\FinishedGood;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use App\Models\Synonim;
use App\Models\User;
use App\Services\MatchingPriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('matching copies all Calc2 periods including zero while preserving current USD and unmapped RM', function () {
    $user = User::factory()->create(['role' => 'superadmin']);
    $rm = RawMaterial::create(['code' => 'RM-A', 'material_id' => 'A', 'description' => 'Material', 'unit' => 'KG', 'currency_type' => 'USD', 'type_rm' => 'LOCAL']);
    $other = RawMaterial::create(['code' => 'RM-B', 'material_id' => 'B', 'description' => 'Other', 'unit' => 'KG', 'currency_type' => 'Rp', 'type_rm' => 'LOCAL']);
    $fg = FinishedGood::create(['code' => 'FG-X', 'description' => 'Source', 'unit_price_le' => 123.45, 'unit_price_qtr_1' => 0, 'unit_price_qtr_2' => 200, 'unit_price_qtr_3' => 300, 'unit_price_qtr_4' => 400]);
    Synonim::create(['raw_material_id' => $rm->id, 'finished_good_id' => $fg->id]);
    foreach (RawMaterialPrice::PERIODS as $period) {
        RawMaterialPrice::create(['raw_material_id' => $rm->id, 'period' => $period, 'rupiah_amount' => 50, 'usd_amount' => 9]);
    }
    $this->actingAs($user)->post(route('admin.calculate.matching-price.store'), ['material_ids' => [$rm->id], 'factory' => 'cikampek', 'period' => 'le', 'search' => 'no-match'])->assertRedirect();
    foreach (['le' => 123.45, 'qtr_1' => 0, 'qtr_2' => 200, 'qtr_3' => 300, 'qtr_4' => 400, 'current' => 50] as $period => $amount) {
        $price = RawMaterialPrice::where('raw_material_id', $rm->id)->where('period', $period)->first();
        expect($price->rupiah_amount)->toBe((float) $amount)->and($price->usd_amount)->toBe(9.0);
        expect($price->source_kind)->toBe($period === 'current' ? 'manual' : 'matching');
    }
    expect($other->prices()->count())->toBe(0);
    expect(DB::table('matching_price_histories')->count())->toBe(5);
    $this->post(route('admin.calculate.matching-price.store'), ['factory' => 'cikampek', 'material_ids' => [$rm->id]])->assertRedirect();
    expect(DB::table('matching_price_histories')->count())->toBe(10);
    $this->get(route('admin.calculate.matching-price.index'))->assertOk()->assertViewHas('history', fn ($history) => $history->count() === 5 && $history->total() === 10);
    $this->get(route('admin.calculate.matching-price.index', ['history_page' => 2]))->assertOk()->assertViewHas('history', fn ($history) => $history->currentPage() === 2 && $history->count() === 5);
});

test('matching creates missing prices using selected factory and records absent old price', function () {
    $user = User::factory()->create(['role' => 'superadmin']);
    $rm = RawMaterial::create(['code' => 'RM-A', 'material_id' => 'A', 'description' => 'Material', 'unit' => 'KG', 'currency_type' => 'Rp', 'type_rm' => 'LOCAL']);
    $fg = FinishedGood::create(['code' => 'FG-X', 'description' => 'Source', 'unit_price_semarang_le' => 777]);
    Synonim::create(['raw_material_id' => $rm->id, 'finished_good_id' => $fg->id]);
    $this->actingAs($user)->post(route('admin.calculate.matching-price.store'), ['factory' => 'semarang', 'material_ids' => [$rm->id]])->assertRedirect();
    expect($rm->prices()->count())->toBe(5);
    $this->assertDatabaseHas('raw_material_prices', ['raw_material_id' => $rm->id, 'period' => 'le', 'rupiah_amount' => 777]);
    $this->assertDatabaseHas('matching_price_histories', ['rm_code' => 'RM-A', 'factory' => 'semarang', 'price_before' => null, 'price_after' => 777]);
    $this->postJson(route('admin.calculate.matching-price.store'), ['factory' => 'unknown'])->assertUnprocessable();
    expect(DB::table('matching_price_histories')->count())->toBe(5);
});

test('matching prices and history roll back together when an audit write fails', function () {
    $user = User::factory()->create(['role' => 'superadmin']);
    $rm = RawMaterial::create(['code' => 'RM-A', 'material_id' => 'A', 'description' => 'Material', 'unit' => 'KG', 'currency_type' => 'Rp', 'type_rm' => 'LOCAL']);
    $fg = FinishedGood::create(['code' => 'FG-X', 'description' => 'Source', 'unit_price_le' => 777]);
    Synonim::create(['raw_material_id' => $rm->id, 'finished_good_id' => $fg->id]);
    DB::statement("CREATE TRIGGER reject_history BEFORE INSERT ON matching_price_histories BEGIN SELECT RAISE(ABORT, 'audit unavailable'); END");
    expect(fn () => app(MatchingPriceService::class)->match('cikampek', $user, [$rm->id]))->toThrow(\Illuminate\Database\QueryException::class);
    expect($rm->prices()->count())->toBe(0)->and(DB::table('matching_price_histories')->count())->toBe(0);
});

test('matching requires superadmin access', function () {
    $this->post(route('admin.calculate.matching-price.store'), ['factory' => 'cikampek'])->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create(['role' => 'user']))->postJson(route('admin.calculate.matching-price.store'), ['factory' => 'cikampek'])->assertForbidden();
});

test('matching only writes selected materials and rejects empty invalid or duplicate selections', function () {
    $user = User::factory()->create(['role' => 'superadmin']);
    $fg = FinishedGood::create(['code' => 'FG-X', 'description' => 'Source', 'unit_price_le' => 777]);
    $materials = collect(['A', 'B'])->map(function ($code) use ($fg) {
        $rm = RawMaterial::create(['code' => 'RM-'.$code, 'material_id' => $code, 'description' => 'Material', 'unit' => 'KG', 'currency_type' => 'Rp', 'type_rm' => 'LOCAL']);
        Synonim::create(['raw_material_id' => $rm->id, 'finished_good_id' => $fg->id]);
        RawMaterialPrice::create(['raw_material_id' => $rm->id, 'period' => 'le', 'rupiah_amount' => 100]);

        return $rm;
    });
    $this->actingAs($user);
    foreach ([[], ['material_ids' => []], ['material_ids' => [999999]], ['material_ids' => [$materials[0]->id, $materials[0]->id]], ['material_ids' => [$materials[0]->id, 999999]]] as $selection) {
        $this->postJson(route('admin.calculate.matching-price.store'), ['factory' => 'cikampek', ...$selection])->assertUnprocessable();
    }
    expect(DB::table('matching_price_histories')->count())->toBe(0);
    expect(fn () => app(MatchingPriceService::class)->match('cikampek', $user, []))->toThrow(\Illuminate\Validation\ValidationException::class);
    expect(fn () => app(MatchingPriceService::class)->match('cikampek', $user, [$materials[0]->id, 999999]))->toThrow(\Illuminate\Validation\ValidationException::class);

    $this->post(route('admin.calculate.matching-price.store'), ['factory' => 'cikampek', 'material_ids' => [$materials[0]->id]])->assertSessionHasNoErrors()->assertRedirect();
    $this->assertDatabaseHas('raw_material_prices', ['raw_material_id' => $materials[0]->id, 'period' => 'le', 'rupiah_amount' => 777]);
    $this->assertDatabaseHas('raw_material_prices', ['raw_material_id' => $materials[1]->id, 'period' => 'le', 'rupiah_amount' => 100]);
    expect($materials[1]->prices()->count())->toBe(1);
    expect(DB::table('matching_price_histories')->count())->toBe(5);
    expect(DB::table('matching_price_histories')->where('rm_code', 'RM-B')->count())->toBe(0);
});
