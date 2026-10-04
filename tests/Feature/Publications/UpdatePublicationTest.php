<?php

declare(strict_types=1);

use App\Enums\PublicationModality;
use App\Models\Category;
use App\Models\Publication;
use App\Models\PublicationImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->owner = User::factory()->create();
    $this->category = Category::factory()->leaf()->create();
});

/**
 * Publicación con fotografías reales en el disco falso.
 *
 * @return array{0: Publication, 1: list<PublicationImage>}
 */
function publicationWithPhotos(User $owner, Category $category, int $photos = 2, string $state = 'donation'): array
{
    $publication = Publication::factory()->ownedBy($owner)->inCategory($category)->{$state}()->create();
    $images = [];

    foreach (range(0, $photos - 1) as $position) {
        $path = "publications/{$publication->id}/foto-{$position}.png";
        Storage::disk('public')->put($path, 'x');
        $images[] = $publication->images()->create(['path' => $path, 'position' => $position]);
    }

    return [$publication->refresh(), $images];
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function updatePayload(Publication $publication, array $overrides = []): array
{
    return array_merge([
        'title' => 'Título actualizado',
        'description' => 'Descripción actualizada del objeto.',
        'category_id' => $publication->category_id,
        'modality' => $publication->modality->value,
        'condition' => $publication->condition->value,
        'price' => $publication->price,
        'wanted_in_exchange' => $publication->wanted_in_exchange,
        'location' => 'Belén, Medellín',
        'image_order' => $publication->images->map(fn ($image) => "existing:{$image->id}")->all(),
    ], $overrides);
}

test('guests are sent to the login page', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category);

    $this->get(route('publications.edit', $publication))->assertRedirect(route('login'));
    $this->put(route('publications.update', $publication), updatePayload($publication))->assertRedirect(route('login'));
});

test('other users cannot edit or update a publication, not even admins', function (string $state) {
    [$publication] = publicationWithPhotos($this->owner, $this->category);
    $intruder = $state === 'admin' ? User::factory()->admin()->create() : User::factory()->create();

    $this->actingAs($intruder)->get(route('publications.edit', $publication))->assertForbidden();
    $this->actingAs($intruder)->put(route('publications.update', $publication), updatePayload($publication))->assertForbidden();

    expect($publication->refresh()->title)->not->toBe('Título actualizado');
})->with(['regular user' => 'user', 'admin' => 'admin']);

test('the owner sees the edit form with the current values', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category, 2);

    $this->actingAs($this->owner)->get(route('publications.edit', $publication))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('publications/edit')
            ->where('publication.slug', $publication->slug)
            ->where('publication.title', $publication->title)
            ->has('publication.images', 2)
            ->has('categories')
            ->has('options.conditions', 3));
});

test('the owner can update the publication and the slug stays the same', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category);
    $slug = $publication->slug;

    $this->actingAs($this->owner)
        ->put(route('publications.update', $publication), updatePayload($publication))
        ->assertRedirect(route('publications.show', $publication));

    $publication->refresh();

    expect($publication->title)->toBe('Título actualizado')
        ->and($publication->location)->toBe('Belén, Medellín')
        ->and($publication->slug)->toBe($slug);
});

test('changing a sale into a donation clears the price', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category, 1, 'sale');

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'modality' => 'donation',
        'price' => '70000',
    ]))->assertSessionHasNoErrors();

    expect($publication->refresh()->modality)->toBe(PublicationModality::Donation)
        ->and($publication->price)->toBeNull();
});

test('changing a donation into a sale requires a price', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category, 1);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, ['modality' => 'sale']))
        ->assertSessionHasErrors('price');

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, ['modality' => 'sale', 'price' => '45000']))
        ->assertSessionHasNoErrors();

    expect($publication->refresh()->price)->toBe(45000);
});

test('photos can be reordered', function () {
    [$publication, [$first, $second]] = publicationWithPhotos($this->owner, $this->category, 2);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'image_order' => ["existing:{$second->id}", "existing:{$first->id}"],
    ]))->assertSessionHasNoErrors();

    expect($publication->images()->pluck('id')->all())->toBe([$second->id, $first->id]);
});

test('a photo can be removed and its file is deleted', function () {
    [$publication, [$first, $second]] = publicationWithPhotos($this->owner, $this->category, 2);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'image_order' => ["existing:{$first->id}"],
    ]))->assertSessionHasNoErrors();

    expect(PublicationImage::whereKey($second->id)->exists())->toBeFalse()
        ->and(PublicationImage::whereKey($first->id)->exists())->toBeTrue();

    Storage::disk('public')->assertMissing($second->path);
    Storage::disk('public')->assertExists($first->path);
});

test('new photos can be added after the existing ones', function () {
    [$publication, [$first]] = publicationWithPhotos($this->owner, $this->category, 1);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'images' => [fakePhoto('nueva.jpg')],
        'image_order' => ["existing:{$first->id}", 'new:0'],
    ]))->assertSessionHasNoErrors();

    $images = $publication->images()->get();

    expect($images)->toHaveCount(2)
        ->and($images[0]->id)->toBe($first->id)
        ->and($images[1]->position)->toBe(1);

    Storage::disk('public')->assertExists($images[1]->path);
});

test('all photos can be replaced', function () {
    [$publication, [$first, $second]] = publicationWithPhotos($this->owner, $this->category, 2);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'images' => [fakePhoto('nueva.png')],
        'image_order' => ['new:0'],
    ]))->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing($first->path);
    Storage::disk('public')->assertMissing($second->path);
    expect($publication->images()->count())->toBe(1);
});

test('the publication cannot end up with more than four photos', function () {
    [$publication, $images] = publicationWithPhotos($this->owner, $this->category, 3);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'images' => [fakePhoto('a.png'), fakePhoto('b.png')],
        'image_order' => [...array_map(fn ($i) => "existing:{$i->id}", $images), 'new:0', 'new:1'],
    ]))->assertSessionHasErrors('image_order');

    expect($publication->images()->count())->toBe(3)
        ->and(Storage::disk('public')->allFiles())->toHaveCount(3);
});

test('the publication cannot be left without photos', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category, 2);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, ['image_order' => []]))
        ->assertSessionHasErrors('image_order');

    expect($publication->images()->count())->toBe(2);
});

test('a photo from another publication cannot be referenced', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category, 1);
    [$other, [$foreign]] = publicationWithPhotos(User::factory()->create(), $this->category, 1);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'image_order' => ["existing:{$foreign->id}"],
    ]))->assertSessionHasErrors('image_order');

    expect($other->images()->count())->toBe(1);
    Storage::disk('public')->assertExists($foreign->path);
});

test('a failed validation leaves photos and files untouched', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category, 2);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'title' => '',
        'images' => [fakePhoto('nueva.png')],
        'image_order' => ['new:0'],
    ]))->assertSessionHasErrors('title');

    expect($publication->images()->count())->toBe(2)
        ->and(Storage::disk('public')->allFiles())->toHaveCount(2);
});

test('a sold publication keeps its modality but can still be edited', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category, 1, 'sold');

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'modality' => 'donation',
    ]))->assertSessionHasErrors(['modality' => 'No puedes cambiar la modalidad de una publicación ya cerrada.']);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication))
        ->assertSessionHasNoErrors();

    expect($publication->refresh()->title)->toBe('Título actualizado');
});

test('status cannot be changed through the update endpoint', function () {
    [$publication] = publicationWithPhotos($this->owner, $this->category, 1);

    $this->actingAs($this->owner)->put(route('publications.update', $publication), updatePayload($publication, [
        'status' => 'sold',
        'user_id' => User::factory()->create()->id,
        'hidden_at' => now()->toDateTimeString(),
    ]));

    $publication->refresh();

    expect($publication->status->value)->toBe('available')
        ->and($publication->user_id)->toBe($this->owner->id)
        ->and($publication->hidden_at)->toBeNull();
});
