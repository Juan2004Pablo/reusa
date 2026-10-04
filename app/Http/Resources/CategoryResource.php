<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            // Se resuelve a un arreglo simple: Inertia envolvería un recurso anidado en `{ data: [...] }`.
            'children' => $this->whenLoaded('children', fn () => self::collection($this->children)->resolve()),
        ];
    }
}
