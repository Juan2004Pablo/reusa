<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\PublicationImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $publication_id
 * @property string $path
 * @property int $position
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Publication $publication
 */
#[Fillable(['path', 'position'])]
class PublicationImage extends Model
{
    /** @use HasFactory<PublicationImageFactory> */
    use HasFactory;

    public const string DISK = 'public';

    /**
     * @return BelongsTo<Publication, $this>
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function url(): string
    {
        return Storage::disk(self::DISK)->url($this->path);
    }
}
