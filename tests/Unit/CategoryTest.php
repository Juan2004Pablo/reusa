<?php

declare(strict_types=1);

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('roots returns only macro categories and leaves only micro categories', function () {
    $macro = Category::factory()->create();
    $micro = Category::factory()->leaf($macro)->create();

    expect(Category::roots()->pluck('id')->all())->toBe([$macro->id])
        ->and(Category::leaves()->pluck('id')->all())->toBe([$micro->id]);
});

test('a category knows whether it is a leaf', function () {
    $macro = Category::factory()->create();
    $micro = Category::factory()->leaf($macro)->create();

    expect($macro->isLeaf())->toBeFalse()
        ->and($micro->isLeaf())->toBeTrue();
});

test('children and parent relations are consistent', function () {
    $macro = Category::factory()->create();
    $micro = Category::factory()->leaf($macro)->create();

    expect($macro->children->pluck('id')->all())->toBe([$micro->id])
        ->and($micro->parent?->is($macro))->toBeTrue();
});

test('the tree is ordered by sort order with ordered children', function () {
    $second = Category::factory()->create(['name' => 'Segunda', 'sort_order' => 2]);
    $first = Category::factory()->create(['name' => 'Primera', 'sort_order' => 1]);
    Category::factory()->leaf($first)->create(['name' => 'B hija', 'sort_order' => 2]);
    Category::factory()->leaf($first)->create(['name' => 'A hija', 'sort_order' => 1]);

    $tree = Category::tree();

    expect($tree->pluck('id')->all())->toBe([$first->id, $second->id])
        ->and($tree->first()?->children->pluck('name')->all())->toBe(['A hija', 'B hija']);
});

test('a category with children cannot be deleted', function () {
    $macro = Category::factory()->create();
    Category::factory()->leaf($macro)->create();

    expect(fn () => $macro->delete())->toThrow(QueryException::class);
});

test('the resource exposes the nested tree without internal columns', function () {
    $macro = Category::factory()->create(['icon' => 'shirt']);
    Category::factory()->leaf($macro)->create(['name' => 'Calzado']);

    $data = CategoryResource::collection(Category::tree())->resolve();

    expect($data)->toHaveCount(1)
        ->and($data[0])->toHaveKeys(['id', 'name', 'slug', 'icon', 'children'])
        ->and($data[0])->not->toHaveKeys(['parent_id', 'created_at'])
        ->and($data[0]['children'])->toHaveCount(1);
});
