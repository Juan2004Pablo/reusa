<?php

declare(strict_types=1);

use App\Models\Publication;
use App\Models\User;

test('guests cannot delete a publication', function () {
    $publication = Publication::factory()->create();

    $this->delete(route('publications.destroy', $publication))->assertRedirect(route('login'));

    expect($publication->refresh()->trashed())->toBeFalse();
});

test('the owner can delete a publication (soft delete)', function () {
    $owner = User::factory()->create();
    $publication = Publication::factory()->ownedBy($owner)->withImages(2)->create();

    $this->actingAs($owner)->delete(route('publications.destroy', $publication))
        ->assertRedirect(route('my-publications.index'));

    $this->assertSoftDeleted($publication);
    expect($publication->images()->count())->toBe(2);
});

test('other users and admins cannot delete someone else\'s publication', function (bool $admin) {
    $publication = Publication::factory()->create();
    $intruder = $admin ? User::factory()->admin()->create() : User::factory()->create();

    $this->actingAs($intruder)->delete(route('publications.destroy', $publication))->assertForbidden();

    expect($publication->refresh()->trashed())->toBeFalse();
})->with([false, true]);

test('a deleted publication returns 404 and disappears from the catalog', function () {
    $owner = User::factory()->create();
    $publication = Publication::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->delete(route('publications.destroy', $publication));

    $this->get(route('publications.show', $publication->slug))->assertNotFound();
    $this->actingAs($owner)->get(route('publications.edit', $publication->slug))->assertNotFound();

    $this->get(route('publications.index'))->assertInertia(
        fn ($page) => $page->has('publications.data', 0)
    );
});
