<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\UserRole;
use App\Models\User;

class ChangeUserRole
{
    public function execute(User $user, UserRole $role): User
    {
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
