<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

/**
 * Genera imágenes PNG sencillas (degradado y formas) sin depender de la extensión GD
 * ni de internet. Se usan como fotografías de ejemplo en los datos de demostración.
 */
final class PlaceholderImage
{
    /**
     * @param  array{0: int, 1: int, 2: int}  $top  Color RGB superior del degradado.
     * @param  array{0: int, 1: int, 2: int}  $bottom  Color RGB inferior del degradado.
     * @param  int  $variant  Cambia la posición de las formas para que cada foto sea distinta.
     */
    public static function png(int $width, int $height, array $top, array $bottom, int $variant = 0): string
    {
        $cx = (int) ($width * (0.28 + 0.44 * ((($variant * 37) % 100) / 100)));
        $cy = (int) ($height * (0.30 + 0.30 * ((($variant * 53) % 100) / 100)));
        $radius = (int) (min($width, $height) * 0.26);
        $boxLeft = (int) ($width * (0.10 + 0.50 * ((($variant * 71) % 100) / 100)));
        $boxTop = (int) ($height * 0.62);
        $boxRight = $boxLeft + (int) ($width * 0.32);
        $boxBottom = $boxTop + (int) ($height * 0.22);

        $raw = '';

        for ($y = 0; $y < $height; $y++) {
            $t = $height > 1 ? $y / ($height - 1) : 0;
            $base = [
                (int) round($top[0] + ($bottom[0] - $top[0]) * $t),
                (int) round($top[1] + ($bottom[1] - $top[1]) * $t),
                (int) round($top[2] + ($bottom[2] - $top[2]) * $t),
            ];

            // La fila se arma con tramos de un solo color en lugar de píxel por píxel.
            $row = str_repeat(self::pixel($base), $width);

            if ($y >= $boxTop && $y <= $boxBottom) {
                $row = self::paint($row, $boxLeft, min($boxRight, $width - 1), self::mix($base, [0, 0, 0], 0.22));
            }

            $dy = $y - $cy;

            if (abs($dy) <= $radius) {
                $half = (int) floor(sqrt($radius * $radius - $dy * $dy));
                $row = self::paint($row, max(0, $cx - $half), min($width - 1, $cx + $half), self::mix($base, [255, 255, 255], 0.38));
            }

            $raw .= "\0".$row; // el byte inicial es el filtro PNG "None"
        }

        return "\x89PNG\r\n\x1a\n"
            .self::chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            .self::chunk('IDAT', (string) gzcompress($raw, 9))
            .self::chunk('IEND', '');
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function pixel(array $rgb): string
    {
        return chr($rgb[0]).chr($rgb[1]).chr($rgb[2]);
    }

    /**
     * Pinta el tramo [$from, $to] (ambos incluidos) de una fila con un color sólido.
     *
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function paint(string $row, int $from, int $to, array $rgb): string
    {
        if ($to < $from) {
            return $row;
        }

        return substr_replace($row, str_repeat(self::pixel($rgb), $to - $from + 1), $from * 3, ($to - $from + 1) * 3);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $from
     * @param  array{0: int, 1: int, 2: int}  $to
     * @return array{0: int, 1: int, 2: int}
     */
    private static function mix(array $from, array $to, float $amount): array
    {
        return [
            (int) round($from[0] + ($to[0] - $from[0]) * $amount),
            (int) round($from[1] + ($to[1] - $from[1]) * $amount),
            (int) round($from[2] + ($to[2] - $from[2]) * $amount),
        ];
    }

    private static function chunk(string $type, string $data): string
    {
        return pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
    }
}
