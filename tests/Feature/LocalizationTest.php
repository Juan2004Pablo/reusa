<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;

test('the application runs in Spanish with the Colombian timezone', function () {
    expect(app()->getLocale())->toBe('es')
        ->and(config('app.fallback_locale'))->toBe('es')
        ->and(config('app.timezone'))->toBe('America/Bogota');
});

test('validation messages are returned in Spanish', function () {
    $errors = Validator::make(['email' => ''], ['email' => 'required'])->errors();

    expect($errors->first('email'))->toBe('Correo electrónico es obligatorio.');
});

test('validation attribute names are translated', function () {
    $errors = Validator::make(['price' => 'abc'], ['price' => 'integer'])->errors();

    expect($errors->first('price'))->toBe('Precio debe ser un número entero.');
});

test('authentication messages are returned in Spanish', function () {
    expect(__('auth.failed'))->toBe('Estas credenciales no coinciden con nuestros registros.')
        ->and(__('auth.inactive'))->toContain('desactivada');
});
