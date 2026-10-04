<?php

declare(strict_types=1);

namespace App\Actions\Publications;

use App\Enums\ItemCondition;
use App\Enums\PublicationModality;

/**
 * Datos de una publicación ya validados y normalizados según su modalidad:
 * el precio solo existe en las ventas y "qué busco a cambio" solo en los intercambios.
 */
final readonly class PublicationData
{
    public function __construct(
        public string $title,
        public string $description,
        public int $categoryId,
        public PublicationModality $modality,
        public ItemCondition $condition,
        public ?int $price,
        public ?string $wantedInExchange,
        public string $location,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $modality = PublicationModality::from((string) $validated['modality']);
        $wanted = isset($validated['wanted_in_exchange']) ? trim((string) $validated['wanted_in_exchange']) : '';

        return new self(
            title: (string) $validated['title'],
            description: (string) $validated['description'],
            categoryId: (int) $validated['category_id'],
            modality: $modality,
            condition: ItemCondition::from((string) $validated['condition']),
            price: $modality->requiresPrice() ? (int) $validated['price'] : null,
            wantedInExchange: $modality->acceptsWantedInExchange() && $wanted !== '' ? $wanted : null,
            location: (string) $validated['location'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'modality' => $this->modality,
            'condition' => $this->condition,
            'price' => $this->price,
            'wanted_in_exchange' => $this->wantedInExchange,
            'location' => $this->location,
        ];
    }
}
