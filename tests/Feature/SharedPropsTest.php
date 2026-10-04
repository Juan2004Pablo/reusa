<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests receive no authenticated user', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.user', null));
});

test('the shared user exposes only safe attributes', function () {
    $user = User::factory()->withContact()->admin()->create();

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.id', $user->id)
            ->where('auth.user.role', 'admin')
            ->where('auth.user.is_admin', true)
            ->has('auth.user', fn (Assert $u) => $u
                ->hasAll(['id', 'name', 'email', 'phone', 'community', 'role', 'is_admin'])
                ->missingAll(['password', 'remember_token', 'is_active', 'terms_accepted_at'])));
});
