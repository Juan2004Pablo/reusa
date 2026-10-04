<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Archivo de imagen real y mínimo (sin necesitar la extensión GD), rellenado hasta `$kilobytes`.
 * El relleno va después del fin del archivo, así que sigue siendo reconocido como imagen.
 */
function fakePhoto(string $name = 'foto.png', int $kilobytes = 5): UploadedFile
{
    $samples = [
        'png' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
        'jpg' => '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=',
        'webp' => 'UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA',
    ];

    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $key = $extension === 'jpeg' ? 'jpg' : $extension;
    $content = base64_decode($samples[$key] ?? $samples['png'], true) ?: '';
    $content = str_pad($content, $kilobytes * 1024, "\0");

    return UploadedFile::fake()->createWithContent($name, $content);
}
