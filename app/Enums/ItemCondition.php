<?php

declare(strict_types=1);

namespace App\Enums;

enum ItemCondition: string
{
    case LikeNew = 'like_new';
    case Good = 'good';
    case Fair = 'fair';

    public function label(): string
    {
        return match ($this) {
            self::LikeNew => 'Como nuevo',
            self::Good => 'Buen estado',
            self::Fair => 'Aceptable',
        };
    }
}
