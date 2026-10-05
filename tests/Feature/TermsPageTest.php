<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Publication;
use App\Models\PublicationImage;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the terms page is public', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('terms'));
});

test('the home page is public and lists the latest available publications', function () {
    Publication::factory()->count(10)->create();
    Publication::factory()->reserved()->create();
    Publication::factory()->hidden()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('latest', 8)
            ->where('availableCount', 10)
            ->has('categories'));
});

test('the home page reports available objects per macro category and impact figures', function () {
    $macro = Category::factory()->create(['sort_order' => 1]);
    $other = Category::factory()->create(['sort_order' => 2]);
    $micro = Category::factory()->leaf($macro)->create();
    $owner = User::factory()->create();

    Publication::factory()->ownedBy($owner)->inCategory($micro)->count(2)->create();
    Publication::factory()->ownedBy($owner)->inCategory($micro)->reserved()->create();
    Publication::factory()->ownedBy($owner)->inCategory($micro)->hidden()->create();
    Publication::factory()->ownedBy($owner)->delivered()->create();
    Publication::factory()->ownedBy($owner)->sold()->create();
    User::factory()->inactive()->create();

    $response = $this->get(route('home'));

    $counts = collect($response->inertiaProps('categories'))->pluck('available_count', 'id');

    expect($counts[$macro->id])->toBe(2)
        ->and($counts[$other->id])->toBe(0)
        ->and($response->inertiaProps('stats'))->toMatchArray([
            'available' => 2,
            'rehomed' => 2,
            'published' => 5,
            'neighbors' => 1,
            'publishers' => 1,
        ])
        ->and($response->inertiaProps('stats')['trends']['published'])->toHaveCount(28)
        ->and(array_sum($response->inertiaProps('stats')['trends']['published']))->toBe(5)
        ->and(array_sum($response->inertiaProps('stats')['trends']['rehomed']))->toBe(2)
        ->and(array_last($response->inertiaProps('stats')['trends']['neighbors']))->toBe(1);
});

test('the home page gives each macro category the cover of its most recent available object', function () {
    $macro = Category::factory()->create(['sort_order' => 1]);
    $empty = Category::factory()->create(['sort_order' => 2]);
    $micro = Category::factory()->leaf($macro)->create();
    $owner = User::factory()->create();

    $older = Publication::factory()->ownedBy($owner)->inCategory($micro)->create(['created_at' => now()->subDay()]);
    PublicationImage::factory()->create(['publication_id' => $older->id, 'path' => 'publications/older.jpg']);
    $newer = Publication::factory()->ownedBy($owner)->inCategory($micro)->create();
    PublicationImage::factory()->create(['publication_id' => $newer->id, 'path' => 'publications/newer.jpg']);
    Publication::factory()->ownedBy($owner)->inCategory($micro)->reserved()->create();

    $covers = collect($this->get(route('home'))->inertiaProps('categories'))->pluck('cover_url', 'id');

    expect($covers[$macro->id])->toContain('publications/newer.jpg')
        ->and($covers[$empty->id])->toBeNull();
});

test('the home page splits available objects by modality', function () {
    Publication::factory()->donation()->count(2)->create();
    Publication::factory()->sale()->create();
    Publication::factory()->donation()->reserved()->create();

    $modalities = collect($this->get(route('home'))->inertiaProps('stats')['modalities'])->pluck('total', 'value');

    expect($modalities->all())->toBe(['donation' => 2, 'exchange' => 0, 'sale' => 1]);
});
