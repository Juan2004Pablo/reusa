<?php

declare(strict_types=1);

use App\Models\Publication;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['name' => 'Moderadora']);
});

/**
 * @param  array<string, mixed>  $query
 * @return list<string>
 */
function moderationTitles(mixed $test, array $query = []): array
{
    return collect($test->actingAs($test->admin)->get(route('admin.publications.index', $query))
        ->inertiaProps('publications.data'))->pluck('title')->all();
}

test('the admin sees every publication, hidden ones included, with their owner', function () {
    $owner = User::factory()->create(['name' => 'Dueña', 'email' => 'duena@example.com']);
    Publication::factory()->ownedBy($owner)->create(['title' => 'Visible', 'created_at' => now()->subDay()]);
    Publication::factory()->ownedBy($owner)->hidden('Spam', $this->admin)->create(['title' => 'Oculta']);

    $this->actingAs($this->admin)->get(route('admin.publications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/publications')
            ->has('publications.data', 2)
            ->where('publications.data.0.owner', ['name' => 'Dueña', 'email' => 'duena@example.com'])
            ->where('publications.data.0.is_hidden', true)
            ->where('publications.data.0.hidden_reason', 'Spam')
            ->where('publications.data.0.hidden_by', 'Moderadora')
            ->where('publications.data.1.is_hidden', false)
            ->has('statuses', 4));
});

test('soft deleted publications are not listed', function () {
    Publication::factory()->create(['title' => 'Borrada'])->delete();

    expect(moderationTitles($this))->toBe([]);
});

test('the moderation list can be filtered by text, status and visibility', function () {
    $ana = User::factory()->create(['name' => 'Ana Gómez', 'email' => 'ana@example.com']);
    Publication::factory()->ownedBy($ana)->create(['title' => 'Mesa de madera']);
    Publication::factory()->reserved()->create(['title' => 'Silla reservada']);
    Publication::factory()->hidden()->create(['title' => 'Lámpara oculta']);

    expect(moderationTitles($this, ['q' => 'mesa']))->toBe(['Mesa de madera'])
        ->and(moderationTitles($this, ['q' => 'ana@example']))->toBe(['Mesa de madera'])
        ->and(moderationTitles($this, ['q' => 'GÓMEZ']))->toBe(['Mesa de madera'])
        ->and(moderationTitles($this, ['status' => 'reserved']))->toBe(['Silla reservada'])
        ->and(moderationTitles($this, ['visibility' => 'hidden']))->toBe(['Lámpara oculta'])
        ->and(moderationTitles($this, ['visibility' => 'visible']))->toHaveCount(2)
        ->and(moderationTitles($this, ['visibility' => 'weird', 'status' => 'nope']))->toHaveCount(3);
});

test('the filters are echoed back', function () {
    $this->actingAs($this->admin)->get(route('admin.publications.index', ['q' => 'x', 'status' => 'sold', 'visibility' => 'hidden']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters', ['q' => 'x', 'status' => 'sold', 'visibility' => 'hidden']));
});

test('an admin can hide a publication with a reason', function () {
    $publication = Publication::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.publications.hide', $publication), ['reason' => '  Producto no admitido  '])
        ->assertSessionHasNoErrors();

    $publication->refresh();

    expect($publication->isHidden())->toBeTrue()
        ->and($publication->hidden_reason)->toBe('Producto no admitido')
        ->and($publication->hidden_by)->toBe($this->admin->id);
});

test('the reason is optional', function (mixed $reason) {
    $publication = Publication::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.publications.hide', $publication), ['reason' => $reason])
        ->assertSessionHasNoErrors();

    expect($publication->refresh()->isHidden())->toBeTrue()
        ->and($publication->hidden_reason)->toBeNull();
})->with([null, '', '   ']);

test('the reason has a maximum length', function () {
    $publication = Publication::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.publications.hide', $publication), ['reason' => str_repeat('a', 256)])
        ->assertSessionHasErrors('reason');

    expect($publication->refresh()->isHidden())->toBeFalse();
});

test('a hidden publication disappears from the catalog, the home page and the public detail', function () {
    $publication = Publication::factory()->create(['title' => 'Será oculta']);
    Publication::factory()->create(['title' => 'Sigue visible']);

    $this->actingAs($this->admin)->post(route('admin.publications.hide', $publication));
    auth()->logout();

    $titles = collect($this->get(route('publications.index'))->inertiaProps('publications.data'))->pluck('title')->all();

    expect($titles)->toBe(['Sigue visible'])
        ->and($this->get(route('home'))->inertiaProps('availableCount'))->toBe(1);

    $this->get(route('publications.show', $publication->slug))->assertNotFound();
    $this->actingAs(User::factory()->create())->get(route('publications.show', $publication->slug))->assertNotFound();
});

test('the owner still sees the hidden publication and the moderation reason', function () {
    $owner = User::factory()->create();
    $publication = Publication::factory()->ownedBy($owner)->create();

    $this->actingAs($this->admin)->post(route('admin.publications.hide', $publication), ['reason' => 'No admitido']);

    $this->actingAs($owner)->get(route('publications.show', $publication->slug))->assertOk();

    $row = $this->actingAs($owner)->get(route('my-publications.index'))->inertiaProps('publications.data.0');

    expect($row['is_hidden'])->toBeTrue()
        ->and($row['hidden_reason'])->toBe('No admitido');
});

test('an admin can show a hidden publication again', function () {
    $publication = Publication::factory()->hidden('Motivo', $this->admin)->create();

    $this->actingAs($this->admin)->delete(route('admin.publications.unhide', $publication))
        ->assertSessionHasNoErrors();

    $publication->refresh();

    expect($publication->isHidden())->toBeFalse()
        ->and($publication->hidden_reason)->toBeNull()
        ->and($publication->hidden_by)->toBeNull();

    auth()->logout();
    $this->get(route('publications.show', $publication->slug))->assertOk();
});

test('hiding an already hidden publication only updates the reason', function () {
    $publication = Publication::factory()->hidden('Motivo viejo')->create();
    $hiddenAt = $publication->hidden_at;

    $this->actingAs($this->admin)->post(route('admin.publications.hide', $publication), ['reason' => 'Motivo nuevo']);

    $publication->refresh();

    expect($publication->hidden_reason)->toBe('Motivo nuevo')
        ->and($publication->hidden_at?->equalTo($hiddenAt))->toBeTrue();
});

test('hiding does not change the status of the publication', function () {
    $publication = Publication::factory()->reserved()->create();

    $this->actingAs($this->admin)->post(route('admin.publications.hide', $publication));

    expect($publication->refresh()->status->value)->toBe('reserved');
});
