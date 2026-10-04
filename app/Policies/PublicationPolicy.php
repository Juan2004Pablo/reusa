<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;

class PublicationPolicy
{
    /** El catálogo es público. */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /** Una publicación oculta solo la ven su dueño y los administradores. */
    public function view(?User $user, Publication $publication): bool
    {
        if (! $publication->isHidden()) {
            return true;
        }

        return $user !== null && ($publication->isOwnedBy($user) || $user->isAdmin());
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Publication $publication): bool
    {
        return $publication->isOwnedBy($user);
    }

    public function delete(User $user, Publication $publication): bool
    {
        return $publication->isOwnedBy($user);
    }

    public function changeStatus(User $user, Publication $publication): bool
    {
        return $publication->isOwnedBy($user);
    }

    /** Ocultar o retirar publicaciones (moderación). */
    public function moderate(User $user, Publication $publication): bool
    {
        return $user->isAdmin();
    }
}
