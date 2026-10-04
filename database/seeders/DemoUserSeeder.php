<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios de demostración. Solo para desarrollo: la contraseña es pública ("password").
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrador ReUsa',
            'email' => 'admin@reusa.test',
            'community' => 'Medellín',
        ]);

        $users = [
            ['Ana Restrepo', 'ana@reusa.test', '300 111 2233', 'Laureles'],
            ['Carlos Gómez', 'carlos@reusa.test', '301 222 3344', 'Belén'],
            ['Laura Mejía', 'laura@reusa.test', '302 333 4455', 'El Poblado'],
            ['Jorge Ríos', 'jorge@reusa.test', null, 'Robledo'],
            ['Marcela Zapata', 'marcela@reusa.test', '304 555 6677', 'Envigado'],
        ];

        foreach ($users as [$name, $email, $phone, $community]) {
            User::factory()->create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'community' => $community,
            ]);
        }
    }
}
