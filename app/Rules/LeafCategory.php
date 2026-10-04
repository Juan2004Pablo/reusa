<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * La categoría debe existir y ser una microcategoría (hoja).
 */
class LeafCategory implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isLeaf = (is_int($value) || (is_string($value) && ctype_digit($value)))
            && Category::query()->leaves()->whereKey((int) $value)->exists();

        if (! $isLeaf) {
            $fail('validation.leaf_category')->translate();
        }
    }
}
