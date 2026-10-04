<?php

declare(strict_types=1);

namespace App\Actions\Publications;

use App\Models\Publication;
use App\Models\PublicationImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Deja las imágenes de la publicación exactamente como indica `$order`.
 *
 * Cada elemento de `$order` es un token: `existing:{id}` conserva una imagen ya guardada
 * y `new:{n}` toma el archivo `$uploads[n]`. El orden del arreglo define la posición.
 * Las imágenes guardadas que no aparecen en `$order` se eliminan (registro y archivo).
 */
class SyncPublicationImages
{
    /**
     * @param  list<string>  $order
     * @param  list<UploadedFile>  $uploads
     */
    public function execute(Publication $publication, array $order, array $uploads): void
    {
        $tokens = $this->parse($order, $uploads);
        $current = $publication->images()->get()->keyBy('id');

        foreach ($tokens as $token) {
            if ($token['type'] === 'existing' && ! $current->has($token['value'])) {
                throw new InvalidArgumentException("La imagen {$token['value']} no pertenece a la publicación.");
            }
        }

        $stored = [];

        try {
            foreach ($tokens as $index => $token) {
                if ($token['type'] === 'new') {
                    $path = $uploads[$token['value']]->store("publications/{$publication->id}", PublicationImage::DISK);

                    if ($path === false) {
                        throw new RuntimeException('No se pudo guardar la imagen.');
                    }

                    $stored[$index] = $path;
                }
            }

            /** @var list<string> $removedPaths */
            $removedPaths = [];

            DB::transaction(function () use ($publication, $tokens, $current, $stored, &$removedPaths): void {
                $keep = collect($tokens)->where('type', 'existing')->pluck('value')->all();

                foreach ($current as $image) {
                    if (! in_array($image->id, $keep, true)) {
                        $removedPaths[] = $image->path;
                        $image->delete();
                    }
                }

                foreach ($tokens as $position => $token) {
                    if ($token['type'] === 'existing') {
                        $current->get($token['value'])?->update(['position' => $position]);
                    } else {
                        $publication->images()->create(['path' => $stored[$position], 'position' => $position]);
                    }
                }
            });
        } catch (Throwable $e) {
            Storage::disk(PublicationImage::DISK)->delete(array_values($stored));

            throw $e;
        }

        Storage::disk(PublicationImage::DISK)->delete($removedPaths);
        $publication->unsetRelation('images');
    }

    /**
     * @param  list<string>  $order
     * @param  list<UploadedFile>  $uploads
     * @return list<array{type: 'existing'|'new', value: int}>
     */
    private function parse(array $order, array $uploads): array
    {
        $tokens = [];

        foreach ($order as $token) {
            if (preg_match('/^(existing|new):(\d+)$/', $token, $m) !== 1) {
                throw new InvalidArgumentException("Token de imagen inválido: {$token}");
            }

            $value = (int) $m[2];

            if ($m[1] === 'new' && ! isset($uploads[$value])) {
                throw new InvalidArgumentException("No existe el archivo nuevo {$value}.");
            }

            $tokens[] = ['type' => $m[1] === 'new' ? 'new' : 'existing', 'value' => $value];
        }

        return $tokens;
    }
}
