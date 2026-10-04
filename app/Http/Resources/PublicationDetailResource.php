<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Publication;
use App\Support\EnumOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Detalle público de una publicación. No expone el correo ni el teléfono del publicante.
 * Requiere `category.parent`, `images` y `user` cargados.
 *
 * @mixin Publication
 */
class PublicationDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $canManage = $viewer !== null && $viewer->can('update', $this->resource);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'modality' => ['value' => $this->modality->value, 'label' => $this->modality->label()],
            'condition' => ['value' => $this->condition->value, 'label' => $this->condition->label()],
            'status' => ['value' => $this->status->value, 'label' => $this->status->label()],
            'price' => $this->price,
            'wanted_in_exchange' => $this->wanted_in_exchange,
            'location' => $this->location,
            'category' => [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'parent' => $this->category->parent === null ? null : [
                    'name' => $this->category->parent->name,
                    'slug' => $this->category->parent->slug,
                ],
            ],
            'images' => $this->images->map(fn ($image) => ['id' => $image->id, 'url' => $image->url()])->values(),
            'owner' => [
                'name' => $this->user->name,
                'community' => $this->user->community,
                'member_since' => $this->user->created_at?->toIso8601String(),
            ],
            'is_hidden' => $this->when($canManage || ($viewer?->isAdmin() ?? false), $this->isHidden()),
            'created_at' => $this->created_at?->toIso8601String(),
            'can' => [
                'update' => $canManage,
                'delete' => $viewer !== null && $viewer->can('delete', $this->resource),
                'change_status' => $viewer !== null && $viewer->can('changeStatus', $this->resource),
            ],
            'status_options' => $canManage
                ? EnumOptions::from($this->status->allowedTransitions($this->modality))
                : [],
        ];
    }
}
