<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Patrones para `LIKE ... ESCAPE '!'` que tratan `%` y `_` como texto literal.
 * Portable entre MySQL, PostgreSQL y SQLite.
 */
final class LikePattern
{
    public const string ESCAPE = '!';

    public static function contains(string $term): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)).'%';
    }
}
