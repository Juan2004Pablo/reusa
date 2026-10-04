<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('anyone can see a publication detail', function () {
    $owner = User::factory()->withContact()->create(['name' => 'Ana Gómez']);
    $macro = Category::factory()->create(['name' => 'Muebles y decoración']);
    $micro = Category::factory()->leaf($macro)->create(['name' => 'Escritorios']);
    $publication = Publication::factory()->ownedBy($owner)->inCategory($micro)->sale(250000)->withImages(3)->create([
        'title' => 'Escritorio de madera',
    ]);

    $this->get(route('publications.show', $publication->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('publications/show')
            ->where('publication.title', 'Escritorio de madera')
            ->where('publication.price', 250000)
            ->where('publication.modality.label', 'Venta')
            ->where('publication.status.value', 'available')
            ->where('publication.category.name', 'Escritorios')
            ->where('publication.category.parent.name', 'Muebles y decoración')
            ->where('publication.owner.name', 'Ana Gómez')
            ->has('publication.images', 3)
            ->where('publication.can.update', false)
            ->where('publication.can.delete', false)
            ->where('publication.status_options', []));
});

test('the detail never exposes the contact details of the owner', function () {
    $owner = User::factory()->withContact()->create();
    $publication = Publication::factory()->ownedBy($owner)->create();

    $owner = $this->get(route('publications.show', $publication->slug))->inertiaProps('publication.owner');

    expect($owner)->toHaveKeys(['name', 'community', 'member_since'])
        ->and($owner)->not->toHaveKeys(['email', 'phone', 'id']);
});

test('reserved, delivered and sold publications can still be opened', function (string $state) {
    $publication = Publication::factory()->{$state}()->create();

    $this->get(route('publications.show', $publication->slug))->assertOk();
})->with(['reserved', 'delivered', 'sold']);

test('the owner gets management flags and the valid next statuses', function () {
    $owner = User::factory()->create();
    $publication = Publication::factory()->ownedBy($owner)->sale()->create();

    $this->actingAs($owner)->get(route('publications.show', $publication->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('publication.can.update', true)
            ->where('publication.can.delete', true)
            ->where('publication.can.change_status', true)
            ->where('publication.status_options', [
                ['value' => 'reserved', 'label' => 'Reservado'],
                ['value' => 'sold', 'label' => 'Vendido'],
            ]));
});

test('another logged in user has no management flags', function () {
    $publication = Publication::factory()->create();

    $this->actingAs(User::factory()->create())->get(route('publications.show', $publication->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('publication.can.update', false)
            ->where('publication.can.change_status', false));
});

test('an unknown slug returns 404', function () {
    $this->get(route('publications.show', 'no-existe-abc123'))->assertNotFound();
});

test('a hidden publication is a 404 for guests and other users', function () {
    $publication = Publication::factory()->hidden('Contenido inapropiado')->create();

    $this->get(route('publications.show', $publication->slug))->assertNotFound();
    $this->actingAs(User::factory()->create())->get(route('publications.show', $publication->slug))->assertNotFound();
});

test('a hidden publication is visible to its owner and to admins', function () {
    $owner = User::factory()->create();
    $publication = Publication::factory()->ownedBy($owner)->hidden()->create();

    $this->actingAs($owner)->get(route('publications.show', $publication->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('publication.is_hidden', true));

    $this->actingAs(User::factory()->admin()->create())->get(route('publications.show', $publication->slug))
        ->assertOk();
});

test('the hidden flag is not part of the public payload', function () {
    $publication = Publication::factory()->create();

    expect($this->get(route('publications.show', $publication->slug))->inertiaProps('publication'))
        ->not->toHaveKey('is_hidden');
});
