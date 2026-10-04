<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\EnumOptions;
use Illuminate\Http\Request;

/**
 * Fila de "Mis publicaciones": la tarjeta más lo necesario para las acciones rápidas.
 */
class MyPublicationResource extends PublicationCardResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'is_hidden' => $this->isHidden(),
            'hidden_reason' => $this->hidden_reason,
            'status_options' => EnumOptions::from($this->status->allowedTransitions($this->modality)),
        ];
    }
}
