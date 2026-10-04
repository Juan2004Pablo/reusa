<?php

declare(strict_types=1);

namespace App\Enums;

enum PublicationSort: string
{
    case Recent = 'recent';
    case PriceAsc = 'price_asc';
    case PriceDesc = 'price_desc';

    public function label(): string
    {
        return match ($this) {
            self::Recent => 'Más recientes',
            self::PriceAsc => 'Precio: menor a mayor',
            self::PriceDesc => 'Precio: mayor a menor',
        };
    }
}
