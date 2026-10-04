<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page from the admin area', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('regular users cannot access the admin area', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admins can access the admin area', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/dashboard'));
});

test('the role cannot be changed through mass assignment', function () {
    $user = User::factory()->create();

    $user->fill(['role' => 'admin', 'is_active' => false]);

    expect($user->isAdmin())->toBeFalse()
        ->and($user->is_active)->toBeTrue();
});
