<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Activar/desactivar cuentas: solo administradores y nunca la propia
     * (un admin no puede dejarse sin acceso).
     */
    public function updateStatus(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->isNot($target);
    }

    /** Cambiar el rol: solo administradores y nunca el propio. */
    public function updateRole(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->isNot($target);
    }
}
