<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Valores iniciales del formulario de edición. Requiere `category` e `images` cargados.
 *
 * @mixin Publication
 */
class PublicationFormResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'parent_category_id' => $this->category->parent_id,
            'modality' => $this->modality->value,
            'condition' => $this->condition->value,
            'price' => $this->price,
            'wanted_in_exchange' => $this->wanted_in_exchange,
            'location' => $this->location,
            'status' => $this->status->value,
            'is_closed' => $this->status->isFinal(),
            'images' => $this->images->map(fn ($image) => ['id' => $image->id, 'url' => $image->url()])->values(),
        ];
    }
}
