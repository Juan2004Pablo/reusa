<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function registrationData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ], $overrides);
}

test('registration screen can be rendered', function () {
    $this->get(route('register'))->assertOk();
});

test('new users can register accepting the terms', function () {
    $response = $this->post(route('register.store'), registrationData());

    $this->assertAuthenticated();
    $response->assertRedirect(route('my-publications.index', absolute: false));

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->terms_accepted_at)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::User)
        ->and($user->is_active)->toBeTrue()
        ->and($user->phone)->toBeNull()
        ->and($user->community)->toBeNull();
});

test('phone and community are stored when provided', function () {
    $this->post(route('register.store'), registrationData([
        'phone' => '300 123 4567',
        'community' => 'Laureles',
    ]));

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->phone)->toBe('300 123 4567')
        ->and($user->community)->toBe('Laureles');
});

test('registration requires accepting the terms', function () {
    $response = $this->post(route('register.store'), registrationData(['terms' => null]));

    $response->assertSessionHasErrors(['terms' => 'Debes aceptar los términos y condiciones para crear tu cuenta.']);
    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('registration rejects a declined terms value', function () {
    $this->post(route('register.store'), registrationData(['terms' => '0']))
        ->assertSessionHasErrors('terms');

    $this->assertGuest();
});

test('registration validates the phone format', function () {
    $this->post(route('register.store'), registrationData(['phone' => 'no-es-un-telefono']))
        ->assertSessionHasErrors('phone');
});

test('registration rejects an email that is already in use', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $this->post(route('register.store'), registrationData())
        ->assertSessionHasErrors(['email' => 'Correo electrónico ya está en uso.']);
});

test('users cannot grant themselves the admin role while registering', function () {
    $this->post(route('register.store'), registrationData([
        'role' => 'admin',
        'is_active' => '0',
    ]));

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::User)
        ->and($user->is_active)->toBeTrue();
});

test('registration is rate limited', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('register.store'), registrationData(['terms' => null]))
            ->assertSessionHasErrors('terms');
    }

    $this->post(route('register.store'), registrationData())->assertTooManyRequests();
    expect(User::count())->toBe(0);
});
