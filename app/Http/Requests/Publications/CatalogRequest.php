<?php

declare(strict_types=1);

namespace App\Http\Requests\Publications;

use App\Enums\PublicationModality;
use App\Enums\PublicationSort;
use App\Enums\PublicationStatus;
use App\Support\PublicationFilters;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Filtros del catálogo (query string). Es una petición GET: si un valor no es válido
 * simplemente se ignora (se usa el valor por defecto) en lugar de redirigir con errores.
 */
class CatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
            'subcategory' => ['nullable', 'string', 'max:120'],
            'modality' => ['nullable', Rule::enum(PublicationModality::class)],
            'status' => ['nullable', Rule::in([...array_column(PublicationStatus::cases(), 'value'), 'all'])],
            'sort' => ['nullable', Rule::enum(PublicationSort::class)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function filters(): PublicationFilters
    {
        // Se descartan los campos que no pasaron la validación; el resto se normaliza.
        $invalid = $this->getValidatorInstance()->errors()->keys();

        return PublicationFilters::fromArray(
            Arr::except($this->only(['q', 'category', 'subcategory', 'modality', 'status', 'sort']), $invalid)
        );
    }

    protected function failedValidation(Validator $validator): void
    {
        // Intencionalmente vacío: los filtros inválidos se descartan (ver `filters()`).
    }
}
