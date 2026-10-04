<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Clasificación inicial: macrocategoría => [icono, [microcategorías]].
     *
     * @var array<string, array{icon: string, children: list<string>}>
     */
    private const array TREE = [
        'Ropa, calzado y accesorios' => [
            'icon' => 'shirt',
            'children' => ['Ropa de mujer', 'Ropa de hombre', 'Ropa infantil', 'Calzado', 'Bolsos y accesorios'],
        ],
        'Libros y material educativo' => [
            'icon' => 'book-open',
            'children' => ['Libros de texto', 'Literatura y novelas', 'Útiles escolares y papelería'],
        ],
        'Muebles y decoración' => [
            'icon' => 'sofa',
            'children' => ['Muebles de sala y comedor', 'Muebles de dormitorio', 'Escritorios y muebles de oficina', 'Decoración y ambientación'],
        ],
        'Hogar y electrodomésticos' => [
            'icon' => 'cooking-pot',
            'children' => ['Menaje de cocina', 'Electrodomésticos pequeños', 'Ropa de cama y textiles del hogar', 'Organizadores y almacenamiento'],
        ],
        'Tecnología y electrónica' => [
            'icon' => 'laptop',
            'children' => ['Computadores y accesorios', 'Celulares y tabletas', 'Audio y video', 'Consolas y videojuegos'],
        ],
        'Herramientas y ferretería' => [
            'icon' => 'wrench',
            'children' => ['Herramientas manuales', 'Herramientas eléctricas', 'Jardinería y exteriores'],
        ],
        'Deportes y tiempo libre' => [
            'icon' => 'bike',
            'children' => ['Equipos deportivos', 'Bicicletas y accesorios', 'Instrumentos musicales', 'Juegos de mesa'],
        ],
        'Juguetes y artículos infantiles' => [
            'icon' => 'blocks',
            'children' => ['Juguetes', 'Juegos didácticos', 'Artículos de entretenimiento infantil'],
        ],
    ];

    /**
     * Idempotente: se puede ejecutar varias veces sin duplicar categorías.
     */
    public function run(): void
    {
        $order = 0;

        foreach (self::TREE as $name => $data) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $data['icon'], 'parent_id' => null, 'sort_order' => $order++],
            );

            foreach ($data['children'] as $position => $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    ['name' => $childName, 'parent_id' => $parent->id, 'sort_order' => $position],
                );
            }
        }
    }
}
