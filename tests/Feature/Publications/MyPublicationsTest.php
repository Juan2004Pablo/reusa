<?php

declare(strict_types=1);

use App\Models\Publication;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are sent to the login page', function () {
    $this->get(route('my-publications.index'))->assertRedirect(route('login'));
});

test('a user sees only their own publications, in any status', function () {
    $me = User::factory()->create();
    Publication::factory()->ownedBy($me)->create(['title' => 'Disponible']);
    Publication::factory()->ownedBy($me)->sold()->create(['title' => 'Vendida']);
    Publication::factory()->ownedBy($me)->hidden('Reportada')->create(['title' => 'Oculta']);
    Publication::factory()->ownedBy($me)->create(['title' => 'Borrada'])->delete();
    Publication::factory()->create(['title' => 'Ajena']);

    $titles = collect($this->actingAs($me)->get(route('my-publications.index'))->inertiaProps('publications.data'))
        ->pluck('title')->all();

    expect($titles)->toEqualCanonicalizing(['Disponible', 'Vendida', 'Oculta']);
});

test('each row carries the quick actions data', function () {
    $me = User::factory()->create();
    Publication::factory()->ownedBy($me)->hidden('Motivo de moderación')->donation()->create();

    $this->actingAs($me)->get(route('my-publications.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('my/publications')
            ->where('publications.data.0.is_hidden', true)
            ->where('publications.data.0.hidden_reason', 'Motivo de moderación')
            ->where('publications.data.0.status_options', [
                ['value' => 'reserved', 'label' => 'Reservado'],
                ['value' => 'delivered', 'label' => 'Entregado'],
            ]));
});

test('counts per status are provided', function () {
    $me = User::factory()->create();
    Publication::factory()->count(2)->ownedBy($me)->create();
    Publication::factory()->ownedBy($me)->reserved()->create();
    Publication::factory()->ownedBy($me)->sold()->create();
    Publication::factory()->count(3)->create();

    $this->actingAs($me)->get(route('my-publications.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('counts', ['available' => 2, 'reserved' => 1, 'delivered' => 0, 'sold' => 1]));
});

test('the list is paginated', function () {
    $me = User::factory()->create();
    Publication::factory()->count(17)->ownedBy($me)->create();

    $response = $this->actingAs($me)->get(route('my-publications.index'));

    expect($response->inertiaProps('publications.data'))->toHaveCount(15)
        ->and($response->inertiaProps('publications.meta.total'))->toBe(17);
});
