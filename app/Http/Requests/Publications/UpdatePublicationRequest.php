<?php

declare(strict_types=1);

namespace App\Http\Requests\Publications;

use App\Enums\PublicationModality;
use App\Models\Publication;
use Illuminate\Validation\Rule;

class UpdatePublicationRequest extends PublicationFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->publication()) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Una publicación cerrada (vendida o entregada) conserva su modalidad.
        if ($this->publication()->status->isFinal()) {
            $rules['modality'] = ['required', Rule::enum(PublicationModality::class), Rule::in([$this->publication()->modality->value])];
        }

        return $rules;
    }

    protected function existingImageIds(): array
    {
        return array_values(array_map(intval(...), $this->publication()->images->pluck('id')->all()));
    }

    private function publication(): Publication
    {
        /** @var Publication $publication */
        $publication = $this->route('publication');

        return $publication->loadMissing('images');
    }
}
