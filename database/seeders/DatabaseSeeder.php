<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Las categorías se siembran siempre; los usuarios y publicaciones de demostración
     * (con contraseñas públicas) solo fuera de producción.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        if (! app()->isProduction()) {
            $this->call([
                DemoUserSeeder::class,
                DemoPublicationSeeder::class,
            ]);
        }
    }
}
