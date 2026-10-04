<?php

declare(strict_types=1);

namespace App\Enums;

enum PublicationStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Delivered = 'delivered';
    case Sold = 'sold';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Reserved => 'Reservado',
            self::Delivered => 'Entregado',
            self::Sold => 'Vendido',
        };
    }

    /** Estados finales: una vez alcanzados no se puede volver atrás. */
    public function isFinal(): bool
    {
        return $this === self::Delivered || $this === self::Sold;
    }

    /**
     * Estado final que corresponde a la modalidad: las ventas se "venden";
     * las donaciones y los intercambios se "entregan".
     */
    public static function closedFor(PublicationModality $modality): self
    {
        return $modality === PublicationModality::Sale ? self::Sold : self::Delivered;
    }

    /**
     * Reglas de transición:
     *  - disponible ↔ reservado
     *  - disponible/reservado → vendido (solo ventas) o entregado (donaciones e intercambios)
     *  - vendido y entregado son finales.
     *
     * @return list<self>
     */
    public function allowedTransitions(PublicationModality $modality): array
    {
        return match ($this) {
            self::Available => [self::Reserved, self::closedFor($modality)],
            self::Reserved => [self::Available, self::closedFor($modality)],
            self::Delivered, self::Sold => [],
        };
    }

    public function canTransitionTo(self $to, PublicationModality $modality): bool
    {
        return in_array($to, $this->allowedTransitions($modality), true);
    }
}
