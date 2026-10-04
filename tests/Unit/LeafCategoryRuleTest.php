<?php

declare(strict_types=1);

use App\Models\Category;
use App\Rules\LeafCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function categoryPasses(mixed $value): bool
{
    return Validator::make(['category_id' => $value], ['category_id' => ['required', new LeafCategory]])->passes();
}

test('accepts a micro category', function () {
    $micro = Category::factory()->leaf()->create();

    expect(categoryPasses($micro->id))->toBeTrue()
        ->and(categoryPasses((string) $micro->id))->toBeTrue();
});

test('rejects a macro category', function () {
    $macro = Category::factory()->create();

    expect(categoryPasses($macro->id))->toBeFalse();
});

test('rejects unknown, empty and malformed values', function (mixed $value) {
    expect(categoryPasses($value))->toBeFalse();
})->with([9999, 0, '', null, 'abc', '1; DROP TABLE categories', [1], 1.5]);

test('fails with a Spanish message', function () {
    $validator = Validator::make(['category_id' => 9999], ['category_id' => [new LeafCategory]]);

    expect($validator->errors()->first('category_id'))->toBe('Selecciona una subcategoría válida.');
});
