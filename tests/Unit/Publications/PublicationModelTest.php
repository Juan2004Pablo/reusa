<?php

declare(strict_types=1);

use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use App\Support\PublicationFilters;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the slug is readable, unique and generated once', function () {
    $first = Publication::factory()->create(['title' => 'Bicicleta de montaña 26"']);
    $second = Publication::factory()->create(['title' => 'Bicicleta de montaña 26"']);

    expect($first->slug)->toMatch('/^bicicleta-de-montana-26-[a-z0-9]{6}$/')
        ->and($second->slug)->not->toBe($first->slug);

    $slug = $first->slug;
    $first->update(['title' => 'Otro título']);

    expect($first->refresh()->slug)->toBe($slug);
});

test('titles without letters still produce a valid slug', function () {
    $publication = Publication::factory()->create(['title' => '???']);

    expect($publication->slug)->toMatch('/^publicacion-[a-z0-9]{6}$/');
});

test('very long titles produce a bounded slug', function () {
    $publication = Publication::factory()->create(['title' => str_repeat('palabra ', 15)]);

    expect(strlen($publication->slug))->toBeLessThanOrEqual(150);
});

test('slugs are the route key', function () {
    $publication = Publication::factory()->create();

    expect($publication->getRouteKeyName())->toBe('slug')
        ->and($publication->getRouteKey())->toBe($publication->slug);
});

test('a new publication is available by default', function () {
    $publication = Publication::factory()->create();

    expect($publication->status)->toBe(PublicationStatus::Available)
        ->and($publication->refresh()->status)->toBe(PublicationStatus::Available);
});

test('status and moderation fields cannot be mass assigned', function () {
    $publication = new Publication([
        'title' => 'Mesa',
        'status' => 'sold',
        'hidden_at' => now(),
        'user_id' => 99,
    ]);

    expect($publication->title)->toBe('Mesa')
        ->and($publication->status)->toBe(PublicationStatus::Available)
        ->and($publication->hidden_at)->toBeNull()
        ->and($publication->user_id)->toBeNull();
});

test('available keeps only available publications', function () {
    Publication::factory()->create();
    Publication::factory()->reserved()->create();
    Publication::factory()->sold()->create();

    expect(Publication::available()->count())->toBe(1);
});

test('visible excludes hidden publications', function () {
    Publication::factory()->create();
    Publication::factory()->hidden()->create();

    expect(Publication::visible()->count())->toBe(1);
});

test('ownedBy keeps only the publications of the user', function () {
    $user = User::factory()->create();
    Publication::factory()->count(2)->ownedBy($user)->create();
    Publication::factory()->create();

    expect(Publication::ownedBy($user)->count())->toBe(2);
});

test('search matches every word in title or description and ignores empty text', function () {
    Publication::factory()->create(['title' => 'Mesa grande', 'description' => 'De roble macizo']);
    Publication::factory()->create(['title' => 'Silla', 'description' => 'Mesa pequeña']);

    expect(Publication::search('mesa')->count())->toBe(2)
        ->and(Publication::search('mesa roble')->count())->toBe(1)
        ->and(Publication::search('')->count())->toBe(2)
        ->and(Publication::search(null)->count())->toBe(2)
        ->and(Publication::search('   ')->count())->toBe(2);
});

test('filter applies the macro and micro category', function () {
    $macro = Category::factory()->create(['slug' => 'macro']);
    $micro = Category::factory()->leaf($macro)->create(['slug' => 'micro']);
    Publication::factory()->inCategory($micro)->create();
    Publication::factory()->create();

    expect(Publication::filter(new PublicationFilters(category: 'macro'))->count())->toBe(1)
        ->and(Publication::filter(new PublicationFilters(subcategory: 'micro'))->count())->toBe(1)
        ->and(Publication::filter(new PublicationFilters(category: 'otra'))->count())->toBe(0);
});

test('filter with no status restriction returns every status', function () {
    Publication::factory()->create();
    Publication::factory()->sold()->create();

    expect(Publication::filter(new PublicationFilters(status: null))->count())->toBe(2)
        ->and(Publication::filter(new PublicationFilters)->count())->toBe(1);
});

test('isOwnedBy handles guests', function () {
    $publication = Publication::factory()->create();

    expect($publication->isOwnedBy(null))->toBeFalse()
        ->and($publication->isOwnedBy($publication->user))->toBeTrue()
        ->and($publication->isOwnedBy(User::factory()->create()))->toBeFalse();
});

test('relations are wired', function () {
    $publication = Publication::factory()->withImages(2)->create();

    expect($publication->user)->toBeInstanceOf(User::class)
        ->and($publication->category->isLeaf())->toBeTrue()
        ->and($publication->images)->toHaveCount(2)
        ->and($publication->images->pluck('position')->all())->toBe([0, 1])
        ->and($publication->user->publications)->toHaveCount(1);
});

test('lazy loading relations is blocked outside production', function () {
    Publication::factory()->count(2)->create();
    $publication = Publication::all()->first();

    expect(fn () => $publication->category->name)->toThrow(LazyLoadingViolationException::class);
});

test('soft deleted publications are excluded by default', function () {
    $publication = Publication::factory()->create();
    $publication->delete();

    expect(Publication::count())->toBe(0)
        ->and(Publication::withTrashed()->count())->toBe(1);
});

test('the hidden state is derived from hidden_at', function () {
    expect(Publication::factory()->make()->isHidden())->toBeFalse()
        ->and(Publication::factory()->hidden()->make()->isHidden())->toBeTrue();
});
