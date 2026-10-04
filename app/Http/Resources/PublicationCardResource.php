<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Datos mínimos de una publicación para tarjetas y listados.
 * Requiere `category` e `images` cargados (eager loading).
 *
 * @mixin Publication
 */
class PublicationCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'modality' => ['value' => $this->modality->value, 'label' => $this->modality->label()],
            'status' => ['value' => $this->status->value, 'label' => $this->status->label()],
            'price' => $this->price,
            'location' => $this->location,
            'category' => ['name' => $this->category->name, 'slug' => $this->category->slug],
            'cover_url' => $this->images->first()?->url(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
