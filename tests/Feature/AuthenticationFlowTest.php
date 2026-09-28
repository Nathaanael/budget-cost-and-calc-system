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
