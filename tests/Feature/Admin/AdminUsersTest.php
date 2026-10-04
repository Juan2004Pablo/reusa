<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Publication;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['name' => 'Zeta Admin']);
});

test('the admin sees the list of users with their publication counts', function () {
    $ana = User::factory()->create(['name' => 'Ana', 'email' => 'ana@example.com']);
    Publication::factory()->count(2)->ownedBy($ana)->create();

    $this->actingAs($this->admin)->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users')
            ->has('users.data', 2)
            ->where('users.data.0.name', 'Ana')
            ->where('users.data.0.publications_count', 2)
            ->where('users.data.0.is_self', false)
            ->where('users.data.1.is_self', true)
            ->has('roles', 2));
});

test('the user list never exposes sensitive attributes', function () {
    $row = $this->actingAs($this->admin)->get(route('admin.users.index'))->inertiaProps('users.data.0');

    expect($row)->not->toHaveKeys(['password', 'remember_token', 'terms_accepted_at', 'email_verified_at']);
});

test('users can be searched by name or email', function () {
    User::factory()->create(['name' => 'Camila Torres', 'email' => 'cami@example.com']);
    User::factory()->create(['name' => 'Pedro Pérez', 'email' => 'pedro@correo.com']);

    $names = fn (array $query) => collect($this->actingAs($this->admin)->get(route('admin.users.index', $query))
        ->inertiaProps('users.data'))->pluck('name')->all();

    expect($names(['q' => 'camila']))->toBe(['Camila Torres'])
        ->and($names(['q' => 'CORREO.COM']))->toBe(['Pedro Pérez'])
        ->and($names(['q' => 'nadie']))->toBe([])
        ->and($names(['q' => '%']))->toBe([])
        ->and($names(['q' => '']))->toHaveCount(3);
});

test('the user list is paginated and keeps the search', function () {
    User::factory()->count(20)->create(['name' => 'Vecino']);

    $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['q' => 'vecino']));

    expect($response->inertiaProps('users.data'))->toHaveCount(15)
        ->and($response->inertiaProps('users.links.next'))->toContain('q=vecino');
});

test('an admin can deactivate and reactivate another user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.users.status.update', $user), ['is_active' => false])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)->patch(route('admin.users.status.update', $user), ['is_active' => true]);

    expect($user->refresh()->is_active)->toBeTrue();
});

test('a deactivated user can no longer log in', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.users.status.update', $user), ['is_active' => false]);
    auth()->logout();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('an admin cannot deactivate their own account', function () {
    $this->actingAs($this->admin)->patch(route('admin.users.status.update', $this->admin), ['is_active' => false])
        ->assertForbidden();

    expect($this->admin->refresh()->is_active)->toBeTrue();
});

test('the status value is validated', function (mixed $value) {
    $user = User::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.users.status.update', $user), ['is_active' => $value])
        ->assertSessionHasErrors('is_active');
})->with([null, 'maybe', ['x']]);

test('an admin can change the role of another user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.users.role.update', $user), ['role' => 'admin'])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->role)->toBe(UserRole::Admin);

    $this->actingAs($this->admin)->patch(route('admin.users.role.update', $user), ['role' => 'user']);

    expect($user->refresh()->role)->toBe(UserRole::User);
});

test('an admin cannot remove their own admin role', function () {
    $this->actingAs($this->admin)->patch(route('admin.users.role.update', $this->admin), ['role' => 'user'])
        ->assertForbidden();

    expect($this->admin->refresh()->isAdmin())->toBeTrue();
});

test('an invalid role is rejected', function (mixed $role) {
    $user = User::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.users.role.update', $user), ['role' => $role])
        ->assertSessionHasErrors('role');

    expect($user->refresh()->role)->toBe(UserRole::User);
})->with(['superadmin', '', null]);

test('a promoted user can access the admin area and a demoted one cannot', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.users.role.update', $user), ['role' => 'admin']);
    $this->actingAs($user->refresh())->get(route('admin.dashboard'))->assertOk();

    $this->actingAs($this->admin)->patch(route('admin.users.role.update', $user), ['role' => 'user']);
    $this->actingAs($user->refresh())->get(route('admin.dashboard'))->assertForbidden();
});

test('at least one admin always remains', function () {
    $other = User::factory()->admin()->create();

    // Cada admin solo puede modificar a los demás: nadie puede quedarse sin el rol por sí mismo.
    $this->actingAs($this->admin)->patch(route('admin.users.role.update', $other), ['role' => 'user'])->assertRedirect();
    $this->actingAs($this->admin)->patch(route('admin.users.role.update', $this->admin), ['role' => 'user'])->assertForbidden();

    expect(User::where('role', UserRole::Admin)->count())->toBe(1);
});
