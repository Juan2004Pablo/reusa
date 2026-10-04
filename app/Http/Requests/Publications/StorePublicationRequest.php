<?php

declare(strict_types=1);

namespace App\Http\Requests\Publications;

use App\Models\Publication;

class StorePublicationRequest extends PublicationFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Publication::class) ?? false;
    }

    protected function existingImageIds(): array
    {
        return [];
    }
}
