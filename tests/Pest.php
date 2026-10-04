<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Los tests Feature usan la base de datos en memoria y la reinician en cada prueba.
| Los tests Unit arrancan la aplicación (para factories, enums y configuración) pero
| solo reinician la base de datos cuando el archivo declara `uses(RefreshDatabase::class)`.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');
