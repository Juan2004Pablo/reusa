<?php

declare(strict_types=1);

use App\Models\User;

test('inactive users cannot log in', function () {
    $user = User::factory()->inactive()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors(['email' => __('auth.inactive')]);
    $this->assertGuest();
});

test('active users still log in normally', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('a wrong password for an inactive user shows the generic error, not the inactive notice', function () {
    $user = User::factory()->inactive()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors(['email' => __('auth.failed')]);
});

test('a user deactivated while logged in is signed out on the next request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('profile.edit'))->assertOk();

    $user->forceFill(['is_active' => false])->save();

    $this->actingAs($user->fresh())
        ->get(route('profile.edit'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
