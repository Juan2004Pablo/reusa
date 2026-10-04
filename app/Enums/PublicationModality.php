<?php

declare(strict_types=1);

namespace App\Enums;

enum PublicationModality: string
{
    case Donation = 'donation';
    case Exchange = 'exchange';
    case Sale = 'sale';

    public function label(): string
    {
        return match ($this) {
            self::Donation => 'Donación',
            self::Exchange => 'Intercambio',
            self::Sale => 'Venta',
        };
    }

    /** El precio en COP es obligatorio solo en las ventas. */
    public function requiresPrice(): bool
    {
        return $this === self::Sale;
    }

    /** "Qué busco a cambio" solo aplica a los intercambios. */
    public function acceptsWantedInExchange(): bool
    {
        return $this === self::Exchange;
    }
}
