<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\PublicationImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DeleteUserAccount
{
    /**
     * Elimina la cuenta con todas sus publicaciones y borra las imágenes del disco
     * (las filas de la base de datos se eliminan en cascada).
     */
    public function execute(User $user): void
    {
        $disk = Storage::disk(PublicationImage::DISK);

        $user->publications()->withTrashed()->pluck('id')->each(
            fn (int $id) => $disk->deleteDirectory("publications/{$id}")
        );

        $user->delete();
    }
}
