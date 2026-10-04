<?php

declare(strict_types=1);

use App\Models\Publication;
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
