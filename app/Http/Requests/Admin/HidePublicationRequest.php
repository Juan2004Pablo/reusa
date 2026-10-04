<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Publication;
use Illuminate\Foundation\Http\FormRequest;

class HidePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Publication $publication */
        $publication = $this->route('publication');

        return $this->user()?->can('moderate', $publication) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
