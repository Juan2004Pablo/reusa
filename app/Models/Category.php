<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Categoría de dos niveles: una macrocategoría (sin padre) agrupa microcategorías.
 * Una publicación solo puede asociarse a una microcategoría (hoja).
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property string|null $icon
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $parent
 * @property-read Collection<int, Category> $children
 */
#[Fillable(['parent_id', 'name', 'slug', 'icon', 'sort_order'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Macrocategorías.
     *
     * @param  Builder<Category>  $query
     */
    #[Scope]
    protected function roots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * Microcategorías (hojas): las únicas asociables a una publicación.
     *
     * @param  Builder<Category>  $query
     */
    #[Scope]
    protected function leaves(Builder $query): void
    {
        $query->whereNotNull('parent_id');
    }

    public function isLeaf(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Árbol completo (macro → micro) ordenado, para formularios y filtros.
     *
     * @return Collection<int, Category>
     */
    public static function tree(): Collection
    {
        return self::query()
            ->roots()
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
