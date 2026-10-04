<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Publication;
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
        ->and($response->inertiaProps('stats'))->toBe(['available' => 2, 'rehomed' => 2, 'neighbors' => 1]);
});
