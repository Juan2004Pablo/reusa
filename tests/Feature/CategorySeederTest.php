<?php

declare(strict_types=1);

use App\Models\Category;
use Database\Seeders\CategorySeeder;

test('the seeder creates 8 macro categories and 30 micro categories', function () {
    $this->seed(CategorySeeder::class);

    expect(Category::roots()->count())->toBe(8)
        ->and(Category::leaves()->count())->toBe(30);
});

test('the seeder is idempotent', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(CategorySeeder::class);

    expect(Category::count())->toBe(38);
});

test('micro categories hang from the right macro category', function () {
    $this->seed(CategorySeeder::class);

    $expected = [
        'Ropa, calzado y accesorios' => ['Ropa de mujer', 'Ropa de hombre', 'Ropa infantil', 'Calzado', 'Bolsos y accesorios'],
        'Libros y material educativo' => ['Libros de texto', 'Literatura y novelas', 'Útiles escolares y papelería'],
        'Muebles y decoración' => ['Muebles de sala y comedor', 'Muebles de dormitorio', 'Escritorios y muebles de oficina', 'Decoración y ambientación'],
        'Hogar y electrodomésticos' => ['Menaje de cocina', 'Electrodomésticos pequeños', 'Ropa de cama y textiles del hogar', 'Organizadores y almacenamiento'],
        'Tecnología y electrónica' => ['Computadores y accesorios', 'Celulares y tabletas', 'Audio y video', 'Consolas y videojuegos'],
        'Herramientas y ferretería' => ['Herramientas manuales', 'Herramientas eléctricas', 'Jardinería y exteriores'],
        'Deportes y tiempo libre' => ['Equipos deportivos', 'Bicicletas y accesorios', 'Instrumentos musicales', 'Juegos de mesa'],
        'Juguetes y artículos infantiles' => ['Juguetes', 'Juegos didácticos', 'Artículos de entretenimiento infantil'],
    ];

    $actual = Category::tree()->mapWithKeys(
        fn (Category $macro) => [$macro->name => $macro->children->pluck('name')->all()]
    )->all();

    expect($actual)->toBe($expected);
});

test('slugs are unique and macro categories have an icon', function () {
    $this->seed(CategorySeeder::class);

    expect(Category::pluck('slug')->unique()->count())->toBe(38)
        ->and(Category::roots()->whereNull('icon')->count())->toBe(0);
});
