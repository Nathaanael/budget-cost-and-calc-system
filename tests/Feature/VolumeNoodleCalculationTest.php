<?php

use App\Models\AreaNoodle;
use App\Models\FinishedGood;
use App\Models\Noodle;
use App\Models\NoodleFormula;
use App\Models\NoodleFormulaItem;
use App\Models\User;
use App\Models\VolumeNoodle;
use App\Services\VolumeNoodleCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('area selection preserves other areas and includes all noodles even when preview is filtered', function () {
    [$area, $otherArea] = volumeCalculationFixture();
    $user = User::factory()->create(['role' => 'superadmin']);
    $service = app(VolumeNoodleCalculationService::class);
    $service->calculate($user->id, $otherArea->id);
    $before = DB::table('finished_good_volumes')->where('area_noodle_id', $otherArea->id)->first();

    $this->actingAs($user)->getJson(route('admin.calculate.volume-noodle.inputs', ['area_id' => $area->id, 'search' => 'X']))
        ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.noodle.code', 'X');
    $this->post(route('admin.calculate.volume-noodle.store'), ['confirm' => 1, 'area_id' => $area->id, 'search' => 'X'])
        ->assertRedirect();
    expect((float) DB::table('finished_good_volumes')->where('area_noodle_id', $area->id)->value('january'))->toBe(3500.0)
        ->and(DB::table('finished_good_volumes')->where('area_noodle_id', $otherArea->id)->first())->toEqual($before);

    VolumeNoodle::where('area_noodle_id', $area->id)->update(['january' => 0, 'le_july' => 0]);
    $service->calculate($user->id, $area->id);
    expect(DB::table('finished_good_volumes')->where('area_noodle_id', $area->id)->count())->toBe(0)
        ->and(DB::table('finished_good_volumes')->where('area_noodle_id', $otherArea->id)->first())->toEqual($before);

    foreach ([[], ['area_id' => 999999], ['area_id' => [$area->id, $otherArea->id]]] as $selection) {
        $this->postJson(route('admin.calculate.volume-noodle.store'), ['confirm' => 1, ...$selection])->assertUnprocessable();
    }
    expect(DB::table('finished_good_volumes')->where('area_noodle_id', $otherArea->id)->first())->toEqual($before);
});

test('input preview defaults empty and paginates selected area only', function () {
    [$area, $otherArea] = volumeCalculationFixture();
    foreach (range(1, 4) as $index) {
        $noodle = Noodle::create(['code' => 'EXTRA-'.$index, 'description' => 'Extra', 'unit' => 'BOX']);
        VolumeNoodle::create(['area_noodle_id' => $area->id, 'noodle_id' => $noodle->id, 'january' => 1]);
    }
    $this->actingAs(User::factory()->create(['role' => 'superadmin']));
    $this->getJson(route('admin.calculate.volume-noodle.inputs'))->assertOk()->assertJsonCount(0, 'data');
    $this->getJson(route('admin.calculate.volume-noodle.inputs', ['area_id' => $area->id]))
        ->assertOk()->assertJsonCount(5, 'data')->assertJsonPath('total', 6)->assertJsonPath('last_page', 2);
    $this->getJson(route('admin.calculate.volume-noodle.inputs', ['area_id' => $area->id, 'input_page' => 2]))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.area_noodle_id', $area->id);
    $this->getJson(route('admin.calculate.volume-noodle.inputs', ['area_id' => $otherArea->id]))
        ->assertOk()->assertJsonCount(2, 'data');
});


function volumeCalculationFixture(): array
{
    $area = AreaNoodle::create(['code' => 'A', 'description' => 'Area A']);
    $otherArea = AreaNoodle::create(['code' => 'B', 'description' => 'Area B']);
    $fg = FinishedGood::create(['code' => 'FG-A', 'description' => 'Shared FG', 'active' => 'N', 'multi_level' => 'Y', 'product_type_1' => 2, 'product_type_2' => 3]);
    foreach (['X' => [1000, 800, 2], 'Y' => [500, 300, 3]] as $code => [$january, $leJuly, $standard]) {
        $noodle = Noodle::create(['code' => $code, 'description' => $code, 'unit' => 'BOX']);
        $formula = NoodleFormula::create(['noodle_id' => $noodle->id]);
        NoodleFormulaItem::create(['noodle_formula_id' => $formula->id, 'finished_good_id' => $fg->id, 'standard' => $standard, 'position' => 1]);
        VolumeNoodle::create(['area_noodle_id' => $area->id, 'noodle_id' => $noodle->id, 'january' => $january, 'le_july' => $leJuly]);
        VolumeNoodle::create(['area_noodle_id' => $otherArea->id, 'noodle_id' => $noodle->id, 'january' => 10]);
    }

    return [$area, $otherArea, $fg];
}

test('volume calculation aggregates by area and FG including inactive multi level FG and replaces results', function () {
    [$area, $otherArea, $fg] = volumeCalculationFixture();
    $user = User::factory()->create(['role' => 'superadmin']);
    $before = DB::table('volume_noodles')->orderBy('id')->get()->toJson();
    $this->actingAs($user)->post(route('admin.calculate.volume-noodle.store'), ['confirm' => 1, 'area_id' => $area->id])
        ->assertRedirect(route('admin.calculate.volume-noodle.index'))->assertSessionHas('success');

    $row = DB::table('finished_good_volumes')->where('area_noodle_id', $area->id)->first();
    expect((float) $row->january)->toBe(3500.0)
        ->and((float) $row->le_july)->toBe(2500.0)
        ->and((float) $row->july)->toBe(0.0)
        ->and((float) $row->total_aop)->toBe(3500.0)
        ->and((float) $row->total_le)->toBe(2500.0)
        ->and($row->product_type_1)->toBe(2)
        ->and(DB::table('finished_good_volumes')->where('area_noodle_id', $otherArea->id)->count())->toBe(0)
        ->and(DB::table('volume_noodles')->orderBy('id')->get()->toJson())->toBe($before)
        ->and(DB::table('volume_calculation_state')->value('calculated_by'))->toBe($user->id);

    app(VolumeNoodleCalculationService::class)->calculate($user->id, AreaNoodle::where('code', 'A')->value('id'));
    expect(DB::table('finished_good_volumes')->count())->toBe(1);

    // Deleted formula items are excluded; zero results remove old rows.
    NoodleFormulaItem::query()->delete();
    app(VolumeNoodleCalculationService::class)->calculate($user->id, AreaNoodle::where('code', 'A')->value('id'));
    expect(DB::table('finished_good_volumes')->count())->toBe(0);
});

test('volume calculation handles every month, decimals, missing formulas and LE-only results', function () {
    $area = AreaNoodle::create(['code' => 'A', 'description' => 'Area']);
    $noodle = Noodle::create(['code' => 'N', 'description' => 'Noodle', 'unit' => 'BOX']);
    $fg = FinishedGood::create(['code' => 'F', 'description' => 'FG']);
    $formula = NoodleFormula::create(['noodle_id' => $noodle->id]);
    NoodleFormulaItem::create(['noodle_formula_id' => $formula->id, 'finished_good_id' => $fg->id, 'standard' => 0.123456, 'position' => 1]);
    $fields = [...VolumeNoodle::MONTH_FIELDS, ...VolumeNoodle::LE_FIELDS];
    $input = VolumeNoodle::create(['area_noodle_id' => $area->id, 'noodle_id' => $noodle->id, ...array_fill_keys($fields, 10.25)]);
    $unmapped = Noodle::create(['code' => 'NONE', 'description' => 'No formula', 'unit' => 'BOX']);
    VolumeNoodle::create(['area_noodle_id' => $area->id, 'noodle_id' => $unmapped->id, 'january' => 999]);
    $user = User::factory()->create(['role' => 'superadmin']);
    $service = app(VolumeNoodleCalculationService::class);
    $service->calculate($user->id, AreaNoodle::where('code', 'A')->value('id'));
    $row = DB::table('finished_good_volumes')->first();
    foreach ($fields as $field) {
        expect((float) $row->{$field})->toBe(1.265424);
    }
    expect((float) $row->total_aop)->toBe(15.185088)->and((float) $row->total_le)->toBe(7.592544);

    $input->update(array_fill_keys(VolumeNoodle::MONTH_FIELDS, 0));
    $service->calculate($user->id, AreaNoodle::where('code', 'A')->value('id'));
    expect(DB::table('finished_good_volumes')->count())->toBe(1)
        ->and((float) DB::table('finished_good_volumes')->value('total_aop'))->toBe(0.0);

    $formula->delete();
    $service->calculate($user->id, AreaNoodle::where('code', 'A')->value('id'));
    expect(DB::table('finished_good_volumes')->count())->toBe(0);
});

test('volume results paginate five by default and preserve period and page size', function () {
    volumeCalculationFixture();
    $user = User::factory()->create(['role' => 'superadmin']);
    $formula = NoodleFormula::first();
    foreach (range(1, 5) as $index) {
        $fg = FinishedGood::create(['code' => 'FG-'.$index, 'description' => 'FG '.$index]);
        NoodleFormulaItem::create(['noodle_formula_id' => $formula->id, 'finished_good_id' => $fg->id, 'standard' => 1, 'position' => $index + 1]);
    }
    app(VolumeNoodleCalculationService::class)->calculate($user->id, AreaNoodle::where('code', 'A')->value('id'));
    app(VolumeNoodleCalculationService::class)->calculate($user->id, AreaNoodle::where('code', 'B')->value('id'));
    $this->actingAs($user)->get(route('admin.calculate.volume-noodle.index'))
        ->assertOk()->assertViewHas('results', fn ($rows) => $rows->count() === 5 && $rows->total() === 12);
    $this->get(route('admin.calculate.volume-noodle.index', ['page' => 2, 'period' => 'le', 'per_page' => 10]))
        ->assertOk()->assertViewHas('period', 'le')
        ->assertViewHas('results', fn ($rows) => $rows->count() === 2 && $rows->currentPage() === 2 && $rows->perPage() === 10);
    $this->get(route('admin.calculate.volume-noodle.index', ['per_page' => 999]))->assertOk()->assertViewHas('perPage', 5);
});

test('volume rebuild rolls back previous results if persistence fails', function () {
    volumeCalculationFixture();
    $user = User::factory()->create(['role' => 'superadmin']);
    $service = app(VolumeNoodleCalculationService::class);
    $service->calculate($user->id, AreaNoodle::where('code', 'A')->value('id'));
    $before = DB::table('finished_good_volumes')->orderBy('id')->get()->toJson();
    $state = DB::table('volume_calculation_state')->first();
    // Force an SQLite insert failure after the transactional DELETE.
    DB::unprepared("CREATE TRIGGER reject_volume_insert BEFORE INSERT ON finished_good_volumes BEGIN SELECT RAISE(ABORT, 'Test insert failure'); END");
    try {
        expect(fn () => $service->calculate($user->id, AreaNoodle::where('code', 'A')->value('id')))->toThrow(\Illuminate\Database\QueryException::class);
        expect(DB::table('finished_good_volumes')->orderBy('id')->get()->toJson())->toBe($before)
            ->and(DB::table('volume_calculation_state')->first())->toEqual($state);
    } finally {
        DB::unprepared('DROP TRIGGER reject_volume_insert');
    }
});

test('volume calculation requires superadmin and explicit confirmation', function () {
    $this->get(route('admin.calculate.volume-noodle.index'))->assertRedirect(route('login'));
    $user = User::factory()->create(['role' => 'user']);
    $this->actingAs($user)->get(route('admin.calculate.volume-noodle.index'))->assertForbidden();
    $this->postJson(route('admin.calculate.volume-noodle.store'), ['confirm' => 1])->assertForbidden();
    $admin = User::factory()->create(['role' => 'superadmin']);
    $this->actingAs($admin)->postJson(route('admin.calculate.volume-noodle.store'))->assertUnprocessable();
    expect(DB::table('volume_calculation_state')->value('calculated_at'))->toBeNull();
});
