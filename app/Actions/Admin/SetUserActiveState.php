<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\User;

class SetUserActiveState
{
    /**
     * Activa o desactiva una cuenta. Una cuenta desactivada no puede iniciar sesión y su
     * sesión abierta se cierra en la siguiente petición (ver EnsureUserIsActive).
     */
    public function execute(User $user, bool $active): User
    {
        $user->forceFill(['is_active' => $active])->save();

        return $user;
    }
}
