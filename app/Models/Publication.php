<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ItemCondition;
use App\Enums\PublicationModality;
use App\Enums\PublicationSort;
use App\Enums\PublicationStatus;
use App\Support\PublicationFilters;
use Carbon\CarbonInterface;
use Database\Factories\PublicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property PublicationModality $modality
 * @property ItemCondition $condition
 * @property int|null $price Precio en COP (solo ventas).
 * @property string|null $wanted_in_exchange
 * @property string $location
 * @property PublicationStatus $status
 * @property CarbonInterface|null $hidden_at
 * @property string|null $hidden_reason
 * @property int|null $hidden_by
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property CarbonInterface|null $deleted_at
 * @property-read User $user
 * @property-read Category $category
 * @property-read Collection<int, PublicationImage> $images
 */
// `status` y los campos de moderación (`hidden_*`) se cambian solo mediante Actions.
#[Fillable(['category_id', 'title', 'description', 'modality', 'condition', 'price', 'wanted_in_exchange', 'location'])]
class Publication extends Model
{
    /** @use HasFactory<PublicationFactory> */
    use HasFactory, SoftDeletes;

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'available',
    ];

    protected static function booted(): void
    {
        static::creating(function (Publication $publication): void {
            if (empty($publication->slug)) {
                $publication->slug = self::generateSlug($publication->title);
            }
        });
    }

    /**
     * Slug legible y único: `bicicleta-montanera-x7k2qd`. Se genera una sola vez y no
     * cambia si luego se edita el título (los enlaces compartidos siguen funcionando).
     */
    public static function generateSlug(string $title): string
    {
        $base = Str::slug(Str::limit($title, 60, ''));
        $base = $base === '' ? 'publicacion' : $base;

        do {
            $slug = $base.'-'.Str::lower(Str::random(6));
        } while (self::withTrashed()->where('slug', $slug)->exists());

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<PublicationImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(PublicationImage::class)->orderBy('position')->orderBy('id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    /**
     * @param  Builder<Publication>  $query
     */
    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where('status', PublicationStatus::Available);
    }

    /**
     * Publicaciones no ocultas por la administración.
     *
     * @param  Builder<Publication>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->whereNull('hidden_at');
    }

    /**
     * @param  Builder<Publication>  $query
     */
    #[Scope]
    protected function ownedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    /**
     * Búsqueda por texto: todas las palabras deben aparecer en el título o la descripción.
     * Usa `LIKE` con carácter de escape explícito, válido en MySQL, PostgreSQL y SQLite.
     *
     * @param  Builder<Publication>  $query
     */
    #[Scope]
    protected function search(Builder $query, ?string $text): void
    {
        $words = $text === null
            ? []
            : (preg_split('/\s+/u', mb_strtolower(trim($text)), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        foreach (array_slice($words, 0, 5) as $word) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%';

            $query->where(function (Builder $q) use ($pattern): void {
                $q->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("LOWER(description) LIKE ? ESCAPE '!'", [$pattern]);
            });
        }
    }

    /**
     * Aplica los filtros y el orden del catálogo.
     *
     * @param  Builder<Publication>  $query
     */
    #[Scope]
    protected function filter(Builder $query, PublicationFilters $filters): void
    {
        $query
            ->search($filters->search)
            ->when($filters->category, fn (Builder $q, string $slug) => $q->whereIn(
                'category_id',
                Category::query()->select('id')->whereHas('parent', fn (Builder $parent) => $parent->where('slug', $slug)),
            ))
            ->when($filters->subcategory, fn (Builder $q, string $slug) => $q->whereIn(
                'category_id',
                Category::query()->select('id')->where('slug', $slug),
            ))
            ->when($filters->modality, fn (Builder $q, PublicationModality $modality) => $q->where('modality', $modality))
            ->when($filters->status, fn (Builder $q, PublicationStatus $status) => $q->where('status', $status));

        match ($filters->sort) {
            // Los objetos sin precio (donaciones e intercambios) van al final.
            PublicationSort::PriceAsc => $query->orderByRaw('price IS NULL')->orderBy('price')->orderByDesc('id'),
            PublicationSort::PriceDesc => $query->orderByRaw('price IS NULL')->orderByDesc('price')->orderByDesc('id'),
            PublicationSort::Recent => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'modality' => PublicationModality::class,
            'condition' => ItemCondition::class,
            'status' => PublicationStatus::class,
            'price' => 'integer',
            'hidden_at' => 'datetime',
        ];
    }
}
