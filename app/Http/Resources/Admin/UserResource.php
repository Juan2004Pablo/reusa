<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Fila de la tabla de usuarios del panel de administración.
 * Requiere `publications_count` (withCount).
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'community' => $this->community,
            'role' => ['value' => $this->role->value, 'label' => $this->role->label()],
            'is_active' => $this->is_active,
            'is_self' => $request->user()?->is($this->resource) ?? false,
            'publications_count' => (int) ($this->getAttribute('publications_count') ?? 0),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
