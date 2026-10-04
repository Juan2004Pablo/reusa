<?php

declare(strict_types=1);

use App\Enums\UserRole;

test('the roles are backed by their database values', function () {
    expect(UserRole::User->value)->toBe('user')
        ->and(UserRole::Admin->value)->toBe('admin')
        ->and(UserRole::from('admin'))->toBe(UserRole::Admin)
        ->and(UserRole::tryFrom('superuser'))->toBeNull();
});

test('only the admin role is an admin', function () {
    expect(UserRole::Admin->isAdmin())->toBeTrue()
        ->and(UserRole::User->isAdmin())->toBeFalse();
});

test('roles have a Spanish label', function () {
    expect(UserRole::User->label())->toBe('Usuario')
        ->and(UserRole::Admin->label())->toBe('Administrador');
});
