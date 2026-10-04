<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Http\Resources\PublicationCardResource;
use Illuminate\Http\Request;

/**
 * Fila de moderación: la tarjeta más el dueño y el estado de ocultamiento.
 * Requiere `category`, `images`, `user` y `hiddenBy` cargados.
 */
class PublicationResource extends PublicationCardResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'owner' => ['name' => $this->user->name, 'email' => $this->user->email],
            'is_hidden' => $this->isHidden(),
            'hidden_reason' => $this->hidden_reason,
            'hidden_at' => $this->hidden_at?->toIso8601String(),
            'hidden_by' => $this->hiddenBy?->name,
        ];
    }
}
