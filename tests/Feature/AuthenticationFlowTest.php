<?php

use App\Models\User;
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

test('superadmin dashboard redirects to user management', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.users.index'));
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

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.noodle.index'))
        ->assertOk()
        ->assertSee('Noodle')
        ->assertSee('Menampilkan')
        ->assertSee('5 / page');

    $this->getJson(route('admin.maintenance.noodle.data', ['search' => 'Pop']))
        ->assertOk()
        ->assertJsonPath('data.0.code', '2000005')
        ->assertJsonCount(2, 'data');

    $this->getJson(route('admin.maintenance.noodle.data', ['unit' => 'Cup']))
        ->assertOk()
        ->assertJsonPath('data.0.description', 'Pop Mie Rasa Ayam')
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
});

test('superadmin can access raw material ui and ajax data', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

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

test('superadmin can access reference ui detail data and create page', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.reference.index'))
        ->assertOk()
        ->assertSee('Reference')
        ->assertSee('Periode Desc')
        ->assertSee('Rate Current')
        ->assertSee('Lihat detail')
        ->assertSee('Edit Reference')
        ->assertSee('Hapus Data Reference?');

    $this->getJson(route('admin.maintenance.reference.data', [
        'search' => 'REF-010',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'REF-010')
        ->assertJsonPath('data.0.rate_current', 16250)
        ->assertJsonPath('data.0.pe_ckp_le', 110)
        ->assertJsonPath('data.0.pe_sby_4', 314)
        ->assertJsonPath('meta.total', 1);

    $this->getJson(route('admin.maintenance.reference.data', [
        'sort' => 'code',
        'direction' => 'desc',
        'per_page' => 10,
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'REF-010')
        ->assertJsonCount(10, 'data');

    $this->get(route('admin.maintenance.reference.create'))
        ->assertOk()
        ->assertSee('Maintenance Reference')
        ->assertSee('Rate LE')
        ->assertSee('PE Ckp')
        ->assertSee('PE Sby');
});

test('superadmin can access factory ui area relations and create page', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.factory.index'))
        ->assertOk()
        ->assertSee('Factory Code')
        ->assertSee('Detail Factory')
        ->assertSee('Lihat detail')
        ->assertSee('Edit Factory')
        ->assertSee('Hapus Data Factory?')
        ->assertSee('C1 - ANCOL');

    $this->getJson(route('admin.maintenance.factory.data', [
        'search' => 'F10',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'F10')
        ->assertJsonPath('data.0.area_1', 'E4')
        ->assertJsonPath('data.0.area_10', 'C6')
        ->assertJsonPath('meta.total', 1);

    $this->getJson(route('admin.maintenance.factory.data', [
        'sort' => 'code',
        'direction' => 'desc',
        'per_page' => 10,
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.code', 'F10')
        ->assertJsonCount(10, 'data');

    $this->get(route('admin.maintenance.factory.create'))
        ->assertOk()
        ->assertSee('Maintenance Factory')
        ->assertSee('#01st Area')
        ->assertSee('#10th Area')
        ->assertSee('W3 - PALEMBANG');
});

test('superadmin can access synonim ui master options and ajax data', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get(route('admin.maintenance.synonim.index'))
        ->assertOk()
        ->assertSee('Synonim')
        ->assertSee('RM Code')
        ->assertSee('FG Code')
        ->assertSee('Edit Synonim')
        ->assertSee('Hapus Data Synonim?');

    $this->getJson(route('admin.maintenance.synonim.data', [
        'search' => 'Flavor Import',
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.rm_code', 'RM-0010')
        ->assertJsonPath('data.0.fg_code', 'FG-0010')
        ->assertJsonPath('meta.total', 1);

    $this->getJson(route('admin.maintenance.synonim.data', [
        'sort' => 'rm_code',
        'direction' => 'desc',
        'per_page' => 10,
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.rm_code', 'RM-0010')
        ->assertJsonCount(10, 'data');

    $this->get(route('admin.maintenance.synonim.create'))
        ->assertOk()
        ->assertSee('Maintenance Synonim')
        ->assertSee('RM-0001 - Tepung Terigu')
        ->assertSee('FG-0001 - Indomie Mi Goreng 5 x 85 gr');
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
});
