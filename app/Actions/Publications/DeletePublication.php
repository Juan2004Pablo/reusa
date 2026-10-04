<?php

declare(strict_types=1);

namespace App\Actions\Publications;

use App\Models\Publication;

class DeletePublication
{
    /**
     * Eliminación lógica (soft delete): conserva los datos y las imágenes.
     */
    public function execute(Publication $publication): void
    {
        $publication->delete();
    }
}
