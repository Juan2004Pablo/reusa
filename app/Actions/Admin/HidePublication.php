<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Publication;
use App\Models\User;

class HidePublication
{
    /**
     * Oculta la publicación del catálogo y del detalle público. Si ya estaba oculta,
     * solo actualiza el motivo y quién la ocultó.
     */
    public function execute(Publication $publication, User $admin, ?string $reason = null): Publication
    {
        $reason = $reason === null ? null : trim($reason);

        $publication->forceFill([
            'hidden_at' => $publication->hidden_at ?? now(),
            'hidden_reason' => $reason === '' ? null : $reason,
            'hidden_by' => $admin->id,
        ])->save();

        return $publication;
    }
}
