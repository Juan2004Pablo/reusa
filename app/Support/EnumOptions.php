<?php

declare(strict_types=1);

namespace App\Support;

use BackedEnum;

/**
 * Convierte los casos de un enum con `label()` en opciones para selects del frontend.
 */
final class EnumOptions
{
    /**
     * @param  list<BackedEnum>  $cases
     * @return list<array{value: string, label: string}>
     */
    public static function from(array $cases): array
    {
        return array_map(
            fn (BackedEnum $case): array => [
                'value' => (string) $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : (string) $case->value,
            ],
            $cases,
        );
    }
}
