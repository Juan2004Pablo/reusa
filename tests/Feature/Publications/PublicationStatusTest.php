<?php

declare(strict_types=1);

use App\Models\Publication;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
});

function publicationFor(User $owner, string $modality, string $status): Publication
{
    $factory = Publication::factory()->ownedBy($owner);

    $factory = match ($modality) {
        'sale' => $factory->sale(),
        'exchange' => $factory->exchange(),
        default => $factory->donation(),
    };

    return $factory->state(['status' => $status])->create();
}

test('the owner can make every valid status change', function (string $modality, string $from, string $to) {
    $publication = publicationFor($this->owner, $modality, $from);

    $this->actingAs($this->owner)->patch(route('publications.status.update', $publication), ['status' => $to])
        ->assertSessionHasNoErrors();

    expect($publication->refresh()->status->value)->toBe($to);
})->with([
    ['donation', 'available', 'reserved'],
    ['donation', 'available', 'delivered'],
    ['donation', 'reserved', 'available'],
    ['donation', 'reserved', 'delivered'],
    ['exchange', 'available', 'reserved'],
    ['exchange', 'available', 'delivered'],
    ['exchange', 'reserved', 'available'],
    ['exchange', 'reserved', 'delivered'],
    ['sale', 'available', 'reserved'],
    ['sale', 'available', 'sold'],
    ['sale', 'reserved', 'available'],
    ['sale', 'reserved', 'sold'],
]);

test('invalid status changes are rejected and nothing changes', function (string $modality, string $from, string $to) {
    $publication = publicationFor($this->owner, $modality, $from);

    $this->actingAs($this->owner)->patch(route('publications.status.update', $publication), ['status' => $to])
        ->assertSessionHasErrors('status');

    expect($publication->refresh()->status->value)->toBe($from);
})->with([
    'a donation cannot be sold' => ['donation', 'available', 'sold'],
    'a reserved donation cannot be sold' => ['donation', 'reserved', 'sold'],
    'an exchange cannot be sold' => ['exchange', 'available', 'sold'],
    'a sale cannot be delivered' => ['sale', 'available', 'delivered'],
    'a reserved sale cannot be delivered' => ['sale', 'reserved', 'delivered'],
    'same status' => ['donation', 'available', 'available'],
    'same status when reserved' => ['sale', 'reserved', 'reserved'],
    'delivered is final' => ['donation', 'delivered', 'available'],
    'delivered cannot be reserved' => ['exchange', 'delivered', 'reserved'],
    'sold is final' => ['sale', 'sold', 'available'],
    'sold cannot be reserved' => ['sale', 'sold', 'reserved'],
]);

test('an unknown status value is rejected', function (mixed $value) {
    $publication = publicationFor($this->owner, 'donation', 'available');

    $this->actingAs($this->owner)->patch(route('publications.status.update', $publication), ['status' => $value])
        ->assertSessionHasErrors('status');
})->with(['archived', '', null, 'AVAILABLE']);

test('the rejection message is in Spanish', function () {
    $publication = publicationFor($this->owner, 'donation', 'available');

    $this->actingAs($this->owner)->patch(route('publications.status.update', $publication), ['status' => 'sold'])
        ->assertSessionHasErrors(['status' => 'No puedes cambiar una publicación de «disponible» a «vendido».']);
});

test('only the owner can change the status', function (string $kind) {
    $publication = publicationFor($this->owner, 'donation', 'available');
    $intruder = match ($kind) {
        'admin' => User::factory()->admin()->create(),
        default => User::factory()->create(),
    };

    $this->actingAs($intruder)->patch(route('publications.status.update', $publication), ['status' => 'reserved'])
        ->assertForbidden();

    expect($publication->refresh()->status->value)->toBe('available');
})->with(['user', 'admin']);

test('guests are sent to the login page', function () {
    $publication = publicationFor($this->owner, 'donation', 'available');

    $this->patch(route('publications.status.update', $publication), ['status' => 'reserved'])
        ->assertRedirect(route('login'));
});

test('a reserved publication can go back to available and then be delivered', function () {
    $publication = publicationFor($this->owner, 'donation', 'available');

    foreach (['reserved', 'available', 'reserved', 'delivered'] as $status) {
        $this->actingAs($this->owner)->patch(route('publications.status.update', $publication), ['status' => $status])
            ->assertSessionHasNoErrors();
    }

    expect($publication->refresh()->status->value)->toBe('delivered');
});
