<?php

use App\Models\AreaNoodle;
use App\Models\FinishedGood;
use App\Models\FinishedGoodFormula;
use App\Models\FinishedGoodFormulaItem;
use App\Models\Factory;
use App\Models\FactoryArea;
use App\Models\Noodle;
use App\Models\NoodleFormula;
use App\Models\NoodleFormulaItem;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use App\Models\Reference;
use App\Models\Synonim;
use App\Models\User;
use App\Models\VolumeNoodle;
use App\Support\AreaNoodleCatalog;
use App\Support\FinishedGoodCatalog;
use App\Support\NoodleCatalog;
use App\Support\RawMaterialCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guest is redirected to login from dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('user with default password is redirected to create a new password', function () {
    $user = User::factory()->create([
        'username' => 'budi.santoso',
        'password' => 'budi.santoso',
        'must_change_password' => true,
    ]);

    $this->post(route('login.store'), [
        'username' => 'budi.santoso',
        'password' => 'budi.santoso',
    ])->assertRedirect(route('password.first.edit'));

    $this->assertAuthenticatedAs($user);
    $this->get(route('dashboard'))->assertRedirect(route('password.first.edit'));
});

test('user can create a personal password after first login', function () {
    $user = User::factory()->create([
        'username' => 'budi.santoso',
        'password' => 'budi.santoso',
        'must_change_password' => true,
    ]);

    $this->actingAs($user)->put(route('password.first.update'), [
        'password' => 'PasswordBaru123',
        'password_confirmation' => 'PasswordBaru123',
    ])->assertRedirect(route('dashboard'));

    expect($user->refresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('PasswordBaru123', $user->password))->toBeTrue();

    $this->get(route('dashboard'))->assertOk();
});

test('personal password cannot be the username', function () {
    $user = User::factory()->create([
        'username' => 'budi1234',
        'must_change_password' => true,
    ]);

    $this->actingAs($user)->put(route('password.first.update'), [
        'password' => 'budi1234',
        'password_confirmation' => 'budi1234',
    ])->assertSessionHasErrors('password');

    expect($user->refresh()->must_change_password)->toBeTrue();
});

test('superadmin can register a user with username as initial password', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)->post(route('admin.users.store'), [
        'name' => 'Siti Aminah',
        'username' => 'siti.aminah',
        'role' => 'user',
    ])->assertRedirect();

    $user = User::where('username', 'siti.aminah')->firstOrFail();

    expect($user->role)->toBe('user')
        ->and($user->email)->toBeNull()
        ->and($user->is_active)->toBeTrue()
        ->and($user->must_change_password)->toBeTrue()
        ->and(Hash::check('siti.aminah', $user->password))->toBeTrue();
});

test('superadmin login opens the personalized dashboard', function () {
    $superadmin = User::factory()->create([
        'name' => 'Nathan Admin',
        'username' => 'nathan.admin',
        'password' => 'Password123',
        'role' => 'superadmin',
    ]);

    $this->post(route('login.store'), [
        'username' => 'nathan.admin',
        'password' => 'Password123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($superadmin);

    $this
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Selamat Datang')
        ->assertSee('Nathan Admin')
        ->assertSee('Waktu Indonesia Barat')
        ->assertSee('Hari ini')
        ->assertSee('Calculate')
        ->assertSee('Purchase Price')
        ->assertSee('Matching Price')
        ->assertSee('U.Cost+U.Price');
});

test('superadmin can search users', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    User::factory()->create([
        'name' => 'Andi Wijaya',
        'username' => 'andi.finance',
        'email' => 'andi@example.com',
    ]);
    User::factory()->create([
        'name' => 'Budi Santoso',
        'username' => 'budi.sales',
        'email' => 'budi@example.com',
    ]);

    $this->actingAs($superadmin)
        ->get(route('admin.users.index', ['search' => 'andi']))
        ->assertOk()
        ->assertSee('andi.finance')
        ->assertDontSee('budi.sales');
});

test('inactive user cannot log in', function () {
    User::factory()->create([
        'username' => 'inactive.user',
        'password' => 'Password123',
        'is_active' => false,
    ]);

    $this->post(route('login.store'), [
        'username' => 'inactive.user',
        'password' => 'Password123',
    ])->assertSessionHasErrors('username');

    $this->assertGuest();
});

test('initial password cannot be reset before user changes it', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $user = User::factory()->create([
        'password' => 'PasswordAwal123',
        'must_change_password' => true,
    ]);

    $this->actingAs($superadmin)
        ->patch(route('admin.users.reset-password', $user))
        ->assertRedirect();

    expect(Hash::check('PasswordAwal123', $user->refresh()->password))->toBeTrue();
});

test('superadmin can toggle status edit and reset a user password', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $user = User::factory()->create(['is_active' => false]);

    $this->actingAs($superadmin)
        ->patch(route('admin.users.toggle-status', $user))
        ->assertRedirect();

    expect($user->refresh()->is_active)->toBeTrue();

    $this->patch(route('admin.users.toggle-status', $user))
        ->assertRedirect();

    expect($user->refresh()->is_active)->toBeFalse();

    $this->put(route('admin.users.update', $user), [
        'username' => 'user.updated',
        'role' => 'superadmin',
    ])->assertRedirect();

    expect($user->refresh()->username)->toBe('user.updated')
        ->and($user->role)->toBe('superadmin');

    $this->patch(route('admin.users.reset-password', $user))
        ->assertRedirect();

    expect($user->refresh()->must_change_password)->toBeTrue()
        ->and(Hash::check('user.updated', $user->password))->toBeTrue();
});

test('regular user cannot access user management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
});

test('superadmin can access dedicated noodle pages', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    NoodleCatalog::all()->each(fn (array $item) => Noodle::create($item));

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.noodle.index'))
        ->assertOk()
        ->assertSee('Noodle')
        ->assertSee('Menampilkan')
        ->assertSee('5 / page')
        ->assertDontSee('Semua Satuan');

    $this->getJson(route('admin.maintenance.noodle.data', ['search' => 'Pop']))
        ->assertOk()
        ->assertJsonPath('data.0.code', '2000005')
        ->assertJsonCount(2, 'data');

    $this->getJson(route('admin.maintenance.noodle.data', [
        'sort' => 'code',
        'direction' => 'desc',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', '2000010')
        ->assertJsonPath('data.1.code', '2000009')
        ->assertJsonPath('data.2.code', '2000008');

    $this->getJson(route('admin.maintenance.noodle.data', [
        'search' => 'Pop',
        'sort' => 'description',
        'direction' => 'asc',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', '2000005')
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.per_page', 5);

    $this->getJson(route('admin.maintenance.noodle.data', ['per_page' => 10]))
        ->assertOk()
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonCount(10, 'data');

    $this->get(route('admin.maintenance.noodle.create'))
        ->assertOk()
        ->assertSee('Tambah Noodle')
        ->assertSee('Noodle Code')
        ->assertSee('Description')
        ->assertSee('Unit');

});

test('superadmin can access area noodle ui and ajax data', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    AreaNoodleCatalog::all()->each(fn (array $item) => AreaNoodle::create($item));

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.area-noodle.index'))
        ->assertOk()
        ->assertSee('Area Noodle')
        ->assertSee('5 / page');

    $this->getJson(route('admin.maintenance.area-noodle.data', [
        'search' => 'MEDAN',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'W1')
        ->assertJsonPath('meta.total', 1);

    $this->getJson(route('admin.maintenance.area-noodle.data', [
        'sort' => 'code',
        'direction' => 'desc',
        'per_page' => 10,
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'W3')
        ->assertJsonCount(10, 'data');

    $this->get(route('admin.maintenance.area-noodle.create'))
        ->assertOk()
        ->assertSee('Tambah Area Noodle Baru')
        ->assertSee('Area Code')
        ->assertSee('Description');
});

test('superadmin can access finished good ui and ajax data', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    FinishedGoodCatalog::all()->each(fn (array $item) => FinishedGood::create($item));

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.finished-good.index'))
        ->assertOk()
        ->assertSee('Finished Good')
        ->assertSee('Product Type')
        ->assertSee('Multi Level')
        ->assertSee('Active');

    $this->getJson(route('admin.maintenance.finished-good.data', [
        'search' => 'Pop Mie',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'FG-0005')
        ->assertJsonPath('data.0.product_type_1', 4)
        ->assertJsonPath('data.0.product_type_2', 8)
        ->assertJsonPath('data.0.multi_level', 'N')
        ->assertJsonPath('data.0.active', 'N')
        ->assertJsonPath('meta.total', 2);

    $this->getJson(route('admin.maintenance.finished-good.data', [
        'sort' => 'code',
        'direction' => 'desc',
        'per_page' => 10,
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'FG-0010')
        ->assertJsonCount(10, 'data');

    $this->get(route('admin.maintenance.finished-good.create'))
        ->assertOk()
        ->assertSee('Tambah Finished Good Baru')
        ->assertSee('Code FG');

    $finishedGood = FinishedGood::where('code', 'FG-0001')->firstOrFail();

    $this->get(route('admin.maintenance.finished-good.edit', $finishedGood))
        ->assertOk()
        ->assertSee('Edit Finished Good')
        ->assertSee('FG-0001')
        ->assertSee('Indomie Mi Goreng 5 x 85 gr');

    $this->put(route('admin.maintenance.finished-good.update', $finishedGood), [
        ...$finishedGood->toArray(),
        'description' => 'Finished Good Halaman Edit',
    ])->assertRedirect(route('admin.maintenance.finished-good.index'));

    expect($finishedGood->fresh()->description)->toBe('Finished Good Halaman Edit');
});

test('superadmin can access raw material ui and ajax data', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    RawMaterialCatalog::all()->each(fn (array $item) => RawMaterial::create($item));

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.raw-material.index'))
        ->assertOk()
        ->assertSee('Raw Material')
        ->assertSee('Code RM')
        ->assertSee('Wastage All')
        ->assertSee('Currency Type');

    $this->getJson(route('admin.maintenance.raw-material.data', [
        'search' => 'Flavor',
        'currency_type' => 'USD',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'RM-0010')
        ->assertJsonPath('data.0.unit', 'Kg')
        ->assertJsonPath('data.0.currency_type', 'USD')
        ->assertJsonPath('meta.total', 1);

    $this->getJson(route('admin.maintenance.raw-material.data', [
        'sort' => 'code',
        'direction' => 'desc',
        'per_page' => 10,
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'RM-0010')
        ->assertJsonCount(10, 'data');

    $this->get(route('admin.maintenance.raw-material.create'))
        ->assertOk()
        ->assertSee('Tambah Raw Material Baru')
        ->assertSee('Type RM');

    $rawMaterial = RawMaterial::where('code', 'RM-0001')->firstOrFail();

    $this->get(route('admin.maintenance.raw-material.edit', $rawMaterial))
        ->assertOk()
        ->assertSee('Edit Raw Material')
        ->assertSee('RM-0001')
        ->assertSee('Tepung Terigu');

    $this->put(route('admin.maintenance.raw-material.update', $rawMaterial), [
        ...$rawMaterial->toArray(),
        'description' => 'Raw Material Halaman Edit',
    ])->assertRedirect(route('admin.maintenance.raw-material.index'));

    expect($rawMaterial->fresh()->description)->toBe('Raw Material Halaman Edit');
});

test('superadmin can manage maintenance master data with random five digit ids', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->actingAs($superadmin);

    $this->post(route('admin.maintenance.noodle.store'), [
        'code' => '12345',
        'description' => 'Kode Terlalu Pendek',
        'unit' => 'Dus',
    ])->assertSessionHasErrors('code');

    $this->post(route('admin.maintenance.noodle.store'), [
        'code' => 'NDL001',
        'description' => 'Kode Bukan Angka',
        'unit' => 'Dus',
    ])->assertSessionHasErrors('code');

    $this->post(route('admin.maintenance.noodle.store'), [
        'code' => '123456',
        'description' => 'Noodle Database',
        'unit' => 'Dus',
    ])->assertRedirect(route('admin.maintenance.noodle.index'));
    $noodle = Noodle::where('code', '123456')->firstOrFail();

    $this->putJson(route('admin.maintenance.noodle.update', $noodle), [
        'code' => '123456',
        'description' => 'Noodle Updated',
        'unit' => 'Cup',
    ])->assertOk()->assertJsonPath('data.description', 'Noodle Updated');

    $this->post(route('admin.maintenance.area-noodle.store'), [
        'code' => 'a1',
        'description' => 'Area Database',
    ])->assertRedirect(route('admin.maintenance.area-noodle.index'));
    $area = AreaNoodle::where('code', 'A1')->firstOrFail();

    $this->post(route('admin.maintenance.raw-material.store'), [
        'code' => '12345',
        'material_id' => 'MAT-INVALID',
        'description' => 'Material Invalid',
        'unit' => 'Kg',
        'wastage_all' => 0,
        'currency_type' => 'Rp',
        'type_rm' => 'LOCAL',
    ])->assertSessionHasErrors('code');

    $this->post(route('admin.maintenance.raw-material.store'), [
        'code' => '654321',
        'material_id' => 'mat-db',
        'description' => 'Material Database',
        'unit' => 'Kg',
        'wastage_all' => 1.25,
        'currency_type' => 'Rp',
        'type_rm' => 'LOCAL',
    ])->assertRedirect(route('admin.maintenance.raw-material.index'));
    $rawMaterial = RawMaterial::where('code', '654321')->firstOrFail();

    $this->post(route('admin.maintenance.finished-good.store'), [
        'code' => 'FG0001',
        'description' => 'Finished Good Invalid',
        'product_type_1' => 1,
        'product_type_2' => 2,
        'multi_level' => 'N',
        'active' => 'Y',
    ])->assertSessionHasErrors('code');

    $this->post(route('admin.maintenance.finished-good.store'), [
        'code' => '789012',
        'description' => 'Finished Good Database',
        'product_type_1' => 1,
        'product_type_2' => 2,
        'selling_price' => '12.500',
        'unit_cost_current' => '3.500',
        'unit_price_current' => '4.000',
        'multi_level' => 'Y',
        'active' => 'Y',
    ])->assertRedirect(route('admin.maintenance.finished-good.index'));
    $finishedGood = FinishedGood::where('code', '789012')->firstOrFail();
    expect($finishedGood->selling_price)->toBe('12500.00')
        ->and($finishedGood->unit_cost_current)->toBe('3500.00')
        ->and($finishedGood->unit_price_current)->toBe('4000.00');

    foreach ([$noodle, $area, $rawMaterial, $finishedGood] as $master) {
        expect($master->id)->toBeGreaterThanOrEqual(10000)->toBeLessThanOrEqual(99999)
            ->and($master->created_by)->toBe($superadmin->id)
            ->and($master->updated_by)->toBe($superadmin->id);
    }

    $this->deleteJson(route('admin.maintenance.noodle.destroy', $noodle))->assertOk();
    $this->deleteJson(route('admin.maintenance.area-noodle.destroy', $area))->assertOk();
    $this->deleteJson(route('admin.maintenance.raw-material.destroy', $rawMaterial))->assertOk();
    $this->deleteJson(route('admin.maintenance.finished-good.destroy', $finishedGood))->assertOk();

    expect(Noodle::find($noodle->id))->toBeNull()
        ->and(AreaNoodle::find($area->id))->toBeNull()
        ->and(RawMaterial::find($rawMaterial->id))->toBeNull()
        ->and(FinishedGood::find($finishedGood->id))->toBeNull();

    $this->post(route('admin.maintenance.area-noodle.store'), [
        'code' => 'a1',
        'description' => 'Area Dibuat Ulang',
    ])->assertRedirect(route('admin.maintenance.area-noodle.index'));

    $recreatedArea = AreaNoodle::where('code', 'A1')->firstOrFail();

    expect($recreatedArea->description)->toBe('Area Dibuat Ulang')
        ->and(AreaNoodle::where('code', 'A1')->count())->toBe(1);
});

test('superadmin can access formula noodle and finished good pages', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.formula.ndl'))
        ->assertOk()
        ->assertSee('Formula NDL')
        ->assertSee('Noodle Code')
        ->assertSee('Tambah Baris')
        ->assertSee('Soft Delete')
        ->assertSee('Hard Delete')
        ->assertSee('Simpan Formula');

    $this->get(route('admin.maintenance.formula.fg'))
        ->assertOk()
        ->assertSee('Formula FG')
        ->assertSee('Code FG')
        ->assertSee('Code RM')
        ->assertSee('Simpan Formula');
});

test('superadmin can lookup and persist a noodle formula', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $noodle = Noodle::create(['code' => '123456', 'description' => 'Noodle Formula', 'unit' => 'Dus']);
    $firstFinishedGood = FinishedGood::create([
        'code' => '654321',
        'description' => 'Finished Good Satu',
        'active' => 'Y',
    ]);
    $secondFinishedGood = FinishedGood::create([
        'code' => '654322',
        'description' => 'Finished Good Dua',
        'active' => 'Y',
    ]);

    $this->actingAs($superadmin)
        ->getJson(route('admin.maintenance.formula.ndl.data', ['code' => $noodle->code]))
        ->assertOk()
        ->assertJsonPath('data.noodle.id', $noodle->id)
        ->assertJsonCount(0, 'data.items');

    $this->putJson(route('admin.maintenance.formula.ndl.update', $noodle), [
        'rows' => [
            ['code' => $firstFinishedGood->code, 'standard' => 1.25, 'deleted' => false],
            ['code' => $secondFinishedGood->code, 'standard' => 0.5, 'deleted' => false],
        ],
    ])->assertOk()
        ->assertJsonPath('data.items.0.code', $firstFinishedGood->code)
        ->assertJsonPath('data.items.0.standard', '1.250000')
        ->assertJsonCount(2, 'data.items');

    $formula = NoodleFormula::where('noodle_id', $noodle->id)->firstOrFail();
    $items = $formula->items()->get();

    expect($formula->id)->toBeGreaterThanOrEqual(10000)->toBeLessThanOrEqual(99999)
        ->and($items)->toHaveCount(2)
        ->and($items->first()->id)->toBeGreaterThanOrEqual(10000)->toBeLessThanOrEqual(99999);

    $this->putJson(route('admin.maintenance.formula.ndl.update', $noodle), [
        'rows' => [
            [
                'id' => $items->first()->id,
                'code' => $firstFinishedGood->code,
                'standard' => 2,
                'deleted' => true,
            ],
        ],
    ])->assertOk()
        ->assertJsonPath('data.items.0.deleted', true)
        ->assertJsonCount(1, 'data.items');

    expect(NoodleFormulaItem::withTrashed()->findOrFail($items->first()->id)->trashed())->toBeTrue()
        ->and(NoodleFormulaItem::withTrashed()->find($items->last()->id))->toBeNull();

    $this->putJson(route('admin.maintenance.formula.ndl.update', $noodle), [
        'rows' => [
            [
                'id' => $items->first()->id,
                'code' => $firstFinishedGood->code,
                'standard' => 2,
                'deleted' => false,
            ],
        ],
    ])->assertOk()->assertJsonPath('data.items.0.deleted', false);

    expect(NoodleFormulaItem::findOrFail($items->first()->id)->standard)->toBe('2.000000');
});

test('superadmin can lookup and persist a finished good formula', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $finishedGood = FinishedGood::create([
        'code' => '765432',
        'description' => 'Finished Good Formula',
        'active' => 'Y',
    ]);
    $firstRawMaterial = RawMaterial::create([
        'code' => '654321',
        'material_id' => 'MAT-001',
        'description' => 'Raw Material Satu',
        'unit' => 'Kg',
        'currency_type' => 'Rp',
        'type_rm' => 'LOCAL',
    ]);
    $secondRawMaterial = RawMaterial::create([
        'code' => '654322',
        'material_id' => 'MAT-002',
        'description' => 'Raw Material Dua',
        'unit' => 'Kg',
        'currency_type' => 'USD',
        'type_rm' => 'IMPORT',
    ]);

    $this->actingAs($superadmin)
        ->getJson(route('admin.maintenance.formula.fg.data', ['code' => $finishedGood->code]))
        ->assertOk()
        ->assertJsonPath('data.master.id', $finishedGood->id)
        ->assertJsonCount(0, 'data.items');

    $this->putJson(route('admin.maintenance.formula.fg.update', $finishedGood), [
        'rows' => [
            ['code' => $firstRawMaterial->code, 'standard' => 0.75, 'deleted' => false],
            ['code' => $secondRawMaterial->code, 'standard' => 0.25, 'deleted' => false],
        ],
    ])->assertOk()
        ->assertJsonPath('data.items.0.code', $firstRawMaterial->code)
        ->assertJsonPath('data.items.0.standard', '0.750000')
        ->assertJsonCount(2, 'data.items');

    $formula = FinishedGoodFormula::where('finished_good_id', $finishedGood->id)->firstOrFail();
    $items = $formula->items()->get();

    expect($formula->id)->toBeGreaterThanOrEqual(10000)->toBeLessThanOrEqual(99999)
        ->and($items)->toHaveCount(2)
        ->and($items->first()->id)->toBeGreaterThanOrEqual(10000)->toBeLessThanOrEqual(99999);

    $this->putJson(route('admin.maintenance.formula.fg.update', $finishedGood), [
        'rows' => [[
            'id' => $items->first()->id,
            'code' => $firstRawMaterial->code,
            'standard' => 1,
            'deleted' => true,
        ]],
    ])->assertOk()
        ->assertJsonPath('data.items.0.deleted', true)
        ->assertJsonCount(1, 'data.items');

    expect(FinishedGoodFormulaItem::withTrashed()->findOrFail($items->first()->id)->trashed())->toBeTrue()
        ->and(FinishedGoodFormulaItem::withTrashed()->find($items->last()->id))->toBeNull();

    $this->putJson(route('admin.maintenance.formula.fg.update', $finishedGood), [
        'rows' => [[
            'id' => $items->first()->id,
            'code' => $firstRawMaterial->code,
            'standard' => 1,
            'deleted' => false,
        ]],
    ])->assertOk()->assertJsonPath('data.items.0.deleted', false);

    expect(FinishedGoodFormulaItem::findOrFail($items->first()->id)->standard)->toBe('1.000000');
});

test('superadmin can access reference ui detail data and create page', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $payload = [
        'code' => '00',
        'description_1' => 'PT.INDOFOOD CBP SUKSES MAKMUR',
        'description_2' => 'FOOD INGREDIENT DIVISION',
        'period' => '01082017',
        'period_description' => 'AGUSTUS 2017',
        'rate_current' => '13.300',
        'rate_le' => '13.300',
        'rate_1' => '13.300',
        'rate_2' => '13.300',
        'rate_3' => '13.300',
        'rate_4' => '13.300',
        'pe_ckp_current' => '1.25',
        'pe_smg_current' => '2.50',
        'pe_sby_current' => '3.75',
    ];

    $this->actingAs($superadmin)
        ->post(route('admin.maintenance.reference.store'), $payload)
        ->assertRedirect(route('admin.maintenance.reference.index'));

    $reference = Reference::where('code', '00')->firstOrFail();

    expect($reference->id)->toBeGreaterThanOrEqual(10000)->toBeLessThanOrEqual(99999)
        ->and($reference->rate_current)->toBe('13300.00')
        ->and($reference->pe_ckp_current)->toBe('1.25')
        ->and($reference->created_by)->toBe($superadmin->id);

    $this->get(route('admin.maintenance.reference.index'))
        ->assertOk()
        ->assertSee('Reference')
        ->assertSee('PT.INDOFOOD CBP SUKSES MAKMUR')
        ->assertSee('Periode Desc')
        ->assertSee('Rate Current')
        ->assertSee('Lihat detail')
        ->assertSee('/edit')
        ->assertSee('Hapus Data Reference?');

    $this->getJson(route('admin.maintenance.reference.data', [
        'search' => 'INDOFOOD',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', '00')
        ->assertJsonPath('data.0.rate_current', '13300.00')
        ->assertJsonPath('data.0.pe_ckp_current', '1.25')
        ->assertJsonPath('data.0.pe_sby_current', '3.75')
        ->assertJsonPath('meta.total', 1);

    $this->putJson(route('admin.maintenance.reference.update', $reference), [
        ...$reference->toArray(),
        'description_2' => 'FOOD INGREDIENT UPDATED',
        'rate_current' => '14.000',
    ])
        ->assertOk()
        ->assertJsonPath('data.description_2', 'FOOD INGREDIENT UPDATED')
        ->assertJsonPath('data.rate_current', '14000.00');

    $this->get(route('admin.maintenance.reference.create'))
        ->assertOk()
        ->assertSee('Maintenance Reference')
        ->assertSee('Rate LE')
        ->assertSee('PE Current')
        ->assertSee('PE Ckp')
        ->assertSee('PE Sby');

    $this->get(route('admin.maintenance.reference.edit', $reference))
        ->assertOk()
        ->assertSee('Edit Reference')
        ->assertSee('PT.INDOFOOD CBP SUKSES MAKMUR')
        ->assertSee('FOOD INGREDIENT UPDATED');

    $this->put(route('admin.maintenance.reference.update', $reference), [
        ...$reference->fresh()->toArray(),
        'description_1' => 'REFERENCE HALAMAN EDIT',
    ])->assertRedirect(route('admin.maintenance.reference.index'));

    expect($reference->fresh()->description_1)->toBe('REFERENCE HALAMAN EDIT');

    $this->deleteJson(route('admin.maintenance.reference.destroy', $reference))->assertOk();

    expect(Reference::find($reference->id))->toBeNull();

    $this->post(route('admin.maintenance.reference.store'), $payload)
        ->assertRedirect(route('admin.maintenance.reference.index'));

    expect(Reference::where('code', '00')->count())->toBe(1);
});

test('superadmin can access factory ui area relations and create page', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $areaC1 = AreaNoodle::create(['code' => 'C1', 'description' => 'ANCOL']);
    $areaC2 = AreaNoodle::create(['code' => 'C2', 'description' => 'CIBITUNG']);
    $areaW1 = AreaNoodle::create(['code' => 'W1', 'description' => 'MEDAN']);
    $payload = [
        'code' => 'S1',
        'description' => 'SEASONING/PWK',
        'area_1' => $areaC1->code,
        'area_2' => $areaC2->code,
        'area_6' => $areaW1->code,
    ];

    $this->actingAs($superadmin)
        ->post(route('admin.maintenance.factory.store'), $payload)
        ->assertRedirect(route('admin.maintenance.factory.index'));

    $factory = Factory::where('code', 'S1')->firstOrFail();
    $slots = FactoryArea::where('factory_id', $factory->id)->orderBy('position')->get();

    expect($factory->id)->toBeGreaterThanOrEqual(10000)->toBeLessThanOrEqual(99999)
        ->and($factory->created_by)->toBe($superadmin->id)
        ->and($slots)->toHaveCount(3)
        ->and($slots->pluck('position')->all())->toBe([1, 2, 6])
        ->and($slots->first()->id)->toBeGreaterThanOrEqual(10000)->toBeLessThanOrEqual(99999);

    $this->get(route('admin.maintenance.factory.index'))
        ->assertOk()
        ->assertSee('Factory Code')
        ->assertSee('Detail Factory')
        ->assertSee('Lihat detail')
        ->assertSee('/edit')
        ->assertSee('Hapus Data Factory?')
        ->assertSee('C1 - ANCOL');

    $this->getJson(route('admin.maintenance.factory.data', [
        'search' => 'SEASONING',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'S1')
        ->assertJsonPath('data.0.description', 'SEASONING/PWK')
        ->assertJsonPath('data.0.area_1', 'C1')
        ->assertJsonPath('data.0.area_6', 'W1')
        ->assertJsonPath('data.0.area_10', null)
        ->assertJsonPath('meta.total', 1);

    $this->putJson(route('admin.maintenance.factory.update', $factory), [
        'code' => 'S1',
        'description' => 'SEASONING/UPDATED',
        'area_1' => 'W1',
        'area_10' => 'C1',
    ])
        ->assertOk()
        ->assertJsonPath('data.description', 'SEASONING/UPDATED')
        ->assertJsonPath('data.area_1', 'W1')
        ->assertJsonPath('data.area_2', null)
        ->assertJsonPath('data.area_10', 'C1');

    $this->get(route('admin.maintenance.factory.create'))
        ->assertOk()
        ->assertSee('Maintenance Factory')
        ->assertSee('#01st Area')
        ->assertSee('#10th Area')
        ->assertSee('C1 - ANCOL');

    $this->get(route('admin.maintenance.factory.edit', $factory))
        ->assertOk()
        ->assertSee('Edit Factory')
        ->assertSee('SEASONING/UPDATED')
        ->assertSee('W1 - MEDAN')
        ->assertSee('C1 - ANCOL');

    $this->put(route('admin.maintenance.factory.update', $factory), [
        'code' => 'S1',
        'description' => 'FACTORY HALAMAN EDIT',
        'area_2' => 'C2',
        'area_9' => 'W1',
    ])->assertRedirect(route('admin.maintenance.factory.index'));

    $factory->refresh()->load('areaSlots.areaNoodle');

    expect($factory->description)->toBe('FACTORY HALAMAN EDIT')
        ->and($factory->areaSlots->firstWhere('position', 2)?->areaNoodle?->code)->toBe('C2')
        ->and($factory->areaSlots->firstWhere('position', 9)?->areaNoodle?->code)->toBe('W1');

    $this->deleteJson(route('admin.maintenance.factory.destroy', $factory))->assertOk();

    expect(Factory::find($factory->id))->toBeNull()
        ->and($factory->areaSlots()->count())->toBe(0);

    $this->post(route('admin.maintenance.factory.store'), $payload)
        ->assertRedirect(route('admin.maintenance.factory.index'));

    expect(Factory::where('code', 'S1')->count())->toBe(1);
});

test('superadmin can manage synonim mappings from database', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $rawMaterialOne = RawMaterial::create([
        'code' => '100001',
        'material_id' => 'MAT-SYN-01',
        'description' => 'Flavor Lokal',
        'unit' => 'KG',
        'wastage_all' => 0,
        'currency_type' => 'Rp',
        'type_rm' => 'Bumbu',
    ]);
    $rawMaterialTwo = RawMaterial::create([
        'code' => '100002',
        'material_id' => 'MAT-SYN-02',
        'description' => 'Flavor Import',
        'unit' => 'KG',
        'wastage_all' => 0,
        'currency_type' => 'USD',
        'type_rm' => 'Bumbu',
    ]);
    $finishedGoodOne = FinishedGood::create([
        'code' => '200001',
        'description' => 'Bumbu Ayam',
    ]);
    $finishedGoodTwo = FinishedGood::create([
        'code' => '200002',
        'description' => 'Bumbu Soto',
    ]);

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.synonim.index'))
        ->assertOk()
        ->assertSee('Synonim')
        ->assertSee('RM Code')
        ->assertSee('FG Code')
        ->assertSee('Edit Synonim')
        ->assertSee('Hapus Data Synonim?');

    $this->get(route('admin.maintenance.synonim.create'))
        ->assertOk()
        ->assertSee('Maintenance Synonim')
        ->assertSee('100001 - Flavor Lokal')
        ->assertSee('200001 - Bumbu Ayam');

    $this->post(route('admin.maintenance.synonim.store'), [
        'rm_code' => '100001',
        'fg_code' => '200001',
    ])->assertRedirect(route('admin.maintenance.synonim.index'));

    $synonim = Synonim::firstOrFail();

    expect($synonim->raw_material_id)->toBe($rawMaterialOne->id)
        ->and($synonim->finished_good_id)->toBe($finishedGoodOne->id)
        ->and($synonim->created_by)->toBe($superadmin->id)
        ->and($synonim->id)->toBeGreaterThanOrEqual(10000)->toBeLessThanOrEqual(99999);

    $this->post(route('admin.maintenance.synonim.store'), [
        'rm_code' => '100001',
        'fg_code' => '200002',
    ])->assertSessionHasErrors('rm_code');

    $this->getJson(route('admin.maintenance.synonim.data', [
        'search' => 'Flavor Lokal',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.id', $synonim->id)
        ->assertJsonPath('data.0.rm_code', '100001')
        ->assertJsonPath('data.0.fg_code', '200001')
        ->assertJsonPath('meta.total', 1);

    $this->putJson(route('admin.maintenance.synonim.update', $synonim), [
        'rm_code' => '100002',
        'fg_code' => '200002',
    ])
        ->assertOk()
        ->assertJsonPath('data.rm_code', '100002')
        ->assertJsonPath('data.rm_description', 'Flavor Import')
        ->assertJsonPath('data.fg_code', '200002')
        ->assertJsonPath('data.fg_description', 'Bumbu Soto');

    $this->deleteJson(route('admin.maintenance.synonim.destroy', $synonim))->assertOk();

    expect(Synonim::find($synonim->id))->toBeNull()
        ->and($rawMaterialTwo->exists)->toBeTrue()
        ->and($finishedGoodTwo->exists)->toBeTrue();
});

test('superadmin can access entry volume noodle and rm price pages', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    AreaNoodleCatalog::all()->each(fn (array $item) => AreaNoodle::create($item));
    NoodleCatalog::all()->each(fn (array $item) => Noodle::create($item));
    $area = AreaNoodle::where('code', 'C1')->firstOrFail();
    $noodle = Noodle::where('code', '2000010')->firstOrFail();
    $payload = [
        'area_noodle_id' => $area->id,
        'noodle_id' => $noodle->id,
        'le_july' => 1400.25,
        'le_august' => 1425.25,
        'le_september' => 1450.25,
        'le_october' => 1475.25,
        'le_november' => 1500.25,
        'le_december' => 1525.25,
        'january' => 2000.50,
        'february' => 2020.50,
        'march' => 2040.50,
        'april' => 2060.50,
        'may' => 2080.50,
        'june' => 2100.50,
        'july' => 2120.50,
        'august' => 2140.50,
        'september' => 2160.50,
        'october' => 2180.50,
        'november' => 2200.50,
        'december' => 2220.50,
    ];

    $this->actingAs($superadmin)
        ->post(route('admin.entry.volume-noodle.store'), $payload)
        ->assertRedirect(route('admin.entry.volume-noodle.index'));

    $volume = VolumeNoodle::firstOrFail();

    expect($volume->area_noodle_id)->toBe($area->id)
        ->and($volume->noodle_id)->toBe($noodle->id)
        ->and($volume->total_le)->toBe(8776.5)
        ->and($volume->total_aop)->toBe(25326.0);

    $this->get(route('admin.entry.volume-noodle.index'))
        ->assertOk()
        ->assertSee('Volume Noodle')
        ->assertSee('AOP')
        ->assertSee('LE Juli')
        ->assertSee('Januari')
        ->assertSee('Edit Volume Noodle')
        ->assertSee('Hapus Volume Noodle?');

    $this->getJson(route('admin.entry.volume-noodle.data', [
        'search' => '2000010',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.noodle_code', '2000010')
        ->assertJsonPath('data.0.area_code', 'C1')
        ->assertJsonPath('data.0.le_july', 1400.25)
        ->assertJsonPath('data.0.total_le', 8776.5)
        ->assertJsonPath('data.0.january', 2000.5)
        ->assertJsonPath('data.0.total_aop', 25326)
        ->assertJsonPath('meta.total', 1);

    $this->getJson(route('admin.entry.volume-noodle.data', [
        'sort' => 'january',
        'direction' => 'desc',
        'per_page' => 10,
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.noodle_code', '2000010')
        ->assertJsonCount(1, 'data');

    $this->get(route('admin.entry.volume-noodle.create'))
        ->assertOk()
        ->assertSee('Maintenance Volume Noodle')
        ->assertSee('C1 - ANCOL')
        ->assertSee('2000001 - Indomie Mi Goreng')
        ->assertSee('AOP');

    $this->putJson(route('admin.entry.volume-noodle.update', $volume), [
        ...$payload,
        'january' => 3000.75,
    ])->assertOk()->assertJsonPath('data.total_aop', 26326.25);

    expect($volume->fresh()->january)->toBe(3000.75)
        ->and($volume->fresh()->total_aop)->toBe(26326.25);

    $this->post(route('admin.entry.volume-noodle.store'), $payload)
        ->assertSessionHasErrors('area_noodle_id');

    $this->deleteJson(route('admin.entry.volume-noodle.destroy', $volume))->assertOk();
    expect(VolumeNoodle::find($volume->id))->toBeNull();

    $this->get(route('admin.entry.rm-price.index'))
        ->assertOk()
        ->assertSee('Entry Raw Material Price');
});

test('superadmin can load and persist raw material prices for every legacy period', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $rawMaterial = RawMaterial::create([
        'code' => '100099',
        'material_id' => 'MAT-PRICE-01',
        'description' => 'Imported Seasoning',
        'unit' => 'KG',
        'wastage_all' => 0.25,
        'currency_type' => 'USD',
        'type_rm' => 'Bumbu',
    ]);
    $payload = [
        'raw_material_id' => $rawMaterial->id,
        'usd_current' => 10.25,
        'rupiah_current' => 165000.50,
        'usd_le' => 11.25,
        'rupiah_le' => 175000.50,
        'usd_qtr_1' => 12.25,
        'rupiah_qtr_1' => 185000.50,
        'usd_qtr_2' => 13.25,
        'rupiah_qtr_2' => 195000.50,
        'usd_qtr_3' => 14.25,
        'rupiah_qtr_3' => 205000.50,
        'usd_qtr_4' => 15.25,
        'rupiah_qtr_4' => 215000.50,
    ];

    $this->actingAs($superadmin)
        ->get(route('admin.entry.rm-price.index'))
        ->assertOk()
        ->assertSee('100099 - Imported Seasoning')
        ->assertSee('Current')
        ->assertSee('Quarter 4');

    $this->getJson(route('admin.entry.rm-price.data', $rawMaterial))
        ->assertOk()
        ->assertJsonPath('raw_material.material_id', 'MAT-PRICE-01')
        ->assertJsonPath('prices.usd_current', 0)
        ->assertJsonPath('prices.rupiah_qtr_4', 0);

    $this->postJson(route('admin.entry.rm-price.store'), $payload)
        ->assertOk()
        ->assertJsonPath('prices.usd_current', 10.25)
        ->assertJsonPath('prices.rupiah_qtr_4', 215000.5);

    expect(RawMaterialPrice::where('raw_material_id', $rawMaterial->id)->count())->toBe(6)
        ->and(RawMaterialPrice::where('raw_material_id', $rawMaterial->id)->where('period', 'le')->value('source_kind'))->toBe('manual');

    $this->getJson(route('admin.entry.rm-price.data', $rawMaterial))
        ->assertOk()
        ->assertJsonPath('prices.usd_le', 11.25)
        ->assertJsonPath('prices.rupiah_qtr_3', 205000.5);

    $this->postJson(route('admin.entry.rm-price.store'), [
        ...$payload,
        'usd_current' => 20.75,
    ])->assertOk()->assertJsonPath('prices.usd_current', 20.75);

    expect(RawMaterialPrice::where('raw_material_id', $rawMaterial->id)->count())->toBe(6)
        ->and(RawMaterialPrice::where('raw_material_id', $rawMaterial->id)->where('period', 'current')->value('usd_amount'))->toBe(20.75);

    $this->postJson(route('admin.entry.rm-price.store'), [
        ...$payload,
        'rupiah_current' => 10000000,
    ])->assertUnprocessable()->assertJsonValidationErrors('rupiah_current');
});

test('superadmin can preview purchase price using reference rates and usd raw materials', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $reference = Reference::create([
        'code' => '00',
        'description_1' => 'PT ICBP',
        'description_2' => 'FOOD INGREDIENT',
        'period' => '01082017',
        'period_description' => 'AGUSTUS 2017',
        'rate_current' => 16000,
        'rate_le' => 16100,
        'rate_1' => 16200,
        'rate_2' => 16300,
        'rate_3' => 16400,
        'rate_4' => 16500,
    ]);
    $usdMaterial = RawMaterial::create([
        'code' => '100101',
        'material_id' => 'MAT-USD-PREVIEW',
        'description' => 'Imported Preview Material',
        'unit' => 'KG',
        'wastage_all' => 0,
        'currency_type' => 'USD',
        'type_rm' => 'IMPORT',
    ]);
    RawMaterial::create([
        'code' => '100102',
        'material_id' => 'MAT-RP-HIDDEN',
        'description' => 'Local Hidden Material',
        'unit' => 'KG',
        'wastage_all' => 0,
        'currency_type' => 'Rp',
        'type_rm' => 'LOCAL',
    ]);
    foreach (RawMaterialPrice::PERIODS as $index => $period) {
        RawMaterialPrice::create([
            'raw_material_id' => $usdMaterial->id,
            'period' => $period,
            'usd_amount' => $period === 'qtr_2' ? 0 : 10.50 + $index,
            'rupiah_amount' => 160000,
            'source_kind' => 'manual',
        ]);
    }

    $this->actingAs($superadmin)
        ->get(route('admin.calculate.purchase-price.index'))
        ->assertOk()
        ->assertSee('Calculate Purchase Price')
        ->assertSee('00 - AGUSTUS 2017 - PT ICBP')
        ->assertSee('Imported Preview Material')
        ->assertSee('MAT-USD-PREVIEW')
        ->assertDontSee('Local Hidden Material')
        ->assertDontSee('MAT-RP-HIDDEN')
        ->assertSee('Calculate &amp; Save', false);

    $this->postJson(route('admin.calculate.purchase-price.store'), [
        'reference_id' => $reference->id,
    ])->assertOk()
        ->assertJsonPath('message', 'Purchase Price berhasil dihitung dan disimpan.')
        ->assertJsonPath('summary.total_materials', 1)
        ->assertJsonPath('summary.updated_materials', 1)
        ->assertJsonPath('summary.updated_prices', 5)
        ->assertJsonPath('summary.skipped_prices', 1)
        ->assertJsonPath('raw_materials.0.prices.current.rupiah', 168000)
        ->assertJsonPath('raw_materials.0.prices.qtr_4.rupiah', 255750)
        ->assertJsonPath('raw_materials.0.prices.qtr_2.rupiah', 160000);

    $calculatedPrice = RawMaterialPrice::where('raw_material_id', $usdMaterial->id)
        ->where('period', 'current')
        ->firstOrFail();

    expect($reference->rate_current)->toBe('16000.00')
        ->and($calculatedPrice->rupiah_amount)->toBe(168000.0)
        ->and($calculatedPrice->source_kind)->toBe('fx')
        ->and($calculatedPrice->reference_id)->toBe($reference->id)
        ->and($calculatedPrice->exchange_rate)->toBe(16000.0)
        ->and($calculatedPrice->calculated_at)->not->toBeNull();

    $this->postJson(route('admin.calculate.purchase-price.store'), [
        'reference_id' => 99999,
    ])->assertUnprocessable()->assertJsonValidationErrors('reference_id');
});

test('regular user cannot access noodle pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.maintenance.noodle.index'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.area-noodle.index'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.finished-good.index'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.raw-material.index'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.formula.ndl'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.formula.fg'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.reference.index'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.reference.create'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.factory.index'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.factory.create'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.synonim.index'))
        ->assertForbidden();

    $this->get(route('admin.maintenance.synonim.create'))
        ->assertForbidden();

    $this->get(route('admin.entry.volume-noodle.index'))
        ->assertForbidden();

    $this->get(route('admin.entry.volume-noodle.create'))
        ->assertForbidden();

    $this->get(route('admin.entry.rm-price.index'))
        ->assertForbidden();
});
