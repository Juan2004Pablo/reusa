<?php

declare(strict_types=1);

namespace App\Http\Requests\Publications;

use App\Actions\Publications\PublicationData;
use App\Enums\ItemCondition;
use App\Enums\PublicationModality;
use App\Rules\LeafCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Reglas comunes de crear y editar una publicación.
 *
 * Las fotografías llegan como `images[]` (archivos nuevos) y `image_order[]`, una lista de
 * tokens que define cuáles se conservan y en qué orden: `existing:{id}` o `new:{índice}`.
 */
abstract class PublicationFormRequest extends FormRequest
{
    /**
     * Identificadores de imágenes ya guardadas que pueden conservarse.
     *
     * @return list<int>
     */
    abstract protected function existingImageIds(): array;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $modality = PublicationModality::tryFrom((string) $this->input('modality'));

        return [
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'category_id' => ['required', new LeafCategory],
            'modality' => ['required', Rule::enum(PublicationModality::class)],
            'condition' => ['required', Rule::enum(ItemCondition::class)],
            'price' => [Rule::requiredIf($modality?->requiresPrice() ?? false), 'nullable', 'integer', 'min:1', 'max:2000000000'],
            'wanted_in_exchange' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:150'],
            'image_order' => ['required', 'array', 'min:1', 'max:4'],
            'image_order.*' => ['required', 'string', 'distinct', 'regex:/^(existing|new):\d+$/'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['image_order', 'image_order.*', 'images', 'images.*'])) {
                return;
            }

            $order = $this->imageOrder();
            $uploads = count($this->uploads());
            $existing = $this->existingImageIds();
            $newTokens = 0;

            foreach ($order as $token) {
                [$type, $value] = explode(':', $token);

                if ($type === 'new') {
                    $newTokens++;
                }

                $valid = $type === 'new' ? (int) $value < $uploads : in_array((int) $value, $existing, true);

                if (! $valid) {
                    $validator->errors()->add('image_order', __('validation.custom.image_order.array'));

                    return;
                }
            }

            if ($newTokens !== $uploads) {
                $validator->errors()->add('image_order', __('validation.custom.image_order.array'));
            }
        }];
    }

    public function publicationData(): PublicationData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return PublicationData::fromValidated($validated);
    }

    /**
     * @return list<string>
     */
    public function imageOrder(): array
    {
        return array_values(array_map('strval', (array) $this->input('image_order', [])));
    }

    /**
     * @return list<UploadedFile>
     */
    public function uploads(): array
    {
        $files = $this->file('images', []);

        return array_values(array_filter((array) $files, fn ($file) => $file instanceof UploadedFile));
    }

    protected function prepareForValidation(): void
    {
        $price = $this->input('price');

        // El precio es un entero en COP: se aceptan separadores y símbolo ("$ 1.500.000").
        // Cualquier otro carácter (p. ej. un signo menos) se deja tal cual para que falle la validación.
        if (is_string($price) && preg_match('/^[\s$.,\d]*$/', $price) === 1) {
            $digits = preg_replace('/\D/', '', $price) ?? '';
            $this->merge(['price' => $digits === '' ? null : $digits]);
        }
    }
}
