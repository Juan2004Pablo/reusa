<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\PublicationModality;
use App\Enums\PublicationSort;
use App\Enums\PublicationStatus;

/**
 * Filtros normalizados del catálogo público.
 *
 * `status` nulo significa "todos los estados" (`?status=all`); por defecto el catálogo
 * solo muestra publicaciones disponibles.
 */
final readonly class PublicationFilters
{
    public function __construct(
        public ?string $search = null,
        public ?string $category = null,
        public ?string $subcategory = null,
        public ?PublicationModality $modality = null,
        public ?PublicationStatus $status = PublicationStatus::Available,
        public PublicationSort $sort = PublicationSort::Recent,
    ) {}

    /**
     * @param  array<string, mixed>  $input  Valores ya validados de la query string.
     */
    public static function fromArray(array $input): self
    {
        $status = $input['status'] ?? null;

        return new self(
            search: self::string($input['q'] ?? null),
            category: self::string($input['category'] ?? null),
            subcategory: self::string($input['subcategory'] ?? null),
            modality: PublicationModality::tryFrom((string) ($input['modality'] ?? '')),
            status: $status === 'all' ? null : (PublicationStatus::tryFrom((string) $status) ?? PublicationStatus::Available),
            sort: PublicationSort::tryFrom((string) ($input['sort'] ?? '')) ?? PublicationSort::Recent,
        );
    }

    /**
     * Palabras del texto de búsqueda (máximo 5), en minúsculas.
     *
     * @return list<string>
     */
    public function searchTerms(): array
    {
        if ($this->search === null) {
            return [];
        }

        $words = preg_split('/\s+/u', mb_strtolower($this->search), -1, PREG_SPLIT_NO_EMPTY);

        return array_slice($words === false ? [] : $words, 0, 5);
    }

    /**
     * Representación para el frontend (refleja la query string actual).
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'q' => $this->search ?? '',
            'category' => $this->category ?? '',
            'subcategory' => $this->subcategory ?? '',
            'modality' => $this->modality->value ?? '',
            'status' => $this->status->value ?? 'all',
            'sort' => $this->sort->value,
        ];
    }

    private static function string(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
