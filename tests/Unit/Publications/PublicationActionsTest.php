<?php

declare(strict_types=1);

use App\Actions\Publications\CreatePublication;
use App\Actions\Publications\DeletePublication;
use App\Actions\Publications\PublicationData;
use App\Actions\Publications\SyncPublicationImages;
use App\Actions\Publications\UpdatePublication;
use App\Actions\Publications\UpdatePublicationStatus;
use App\Enums\PublicationModality;
use App\Enums\PublicationStatus;
use App\Exceptions\InvalidStatusTransition;
use App\Models\Category;
use App\Models\Publication;
use App\Models\PublicationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

function actionData(Category $category, string $modality = 'sale'): PublicationData
{
    return PublicationData::fromValidated([
        'title' => 'Mesa de centro',
        'description' => 'Mesa de centro en madera.',
        'category_id' => $category->id,
        'modality' => $modality,
        'condition' => 'good',
        'price' => '80000',
        'wanted_in_exchange' => 'Una lámpara',
        'location' => 'Envigado',
    ]);
}

test('CreatePublication stores the publication with its photos for the owner', function () {
    $owner = User::factory()->create();
    $category = Category::factory()->leaf()->create();

    $publication = app(CreatePublication::class)->execute(
        $owner,
        actionData($category),
        ['new:1', 'new:0'],
        [fakePhoto('a.png'), fakePhoto('b.jpg')],
    );

    expect($publication->exists)->toBeTrue()
        ->and($publication->user_id)->toBe($owner->id)
        ->and($publication->modality)->toBe(PublicationModality::Sale)
        ->and($publication->price)->toBe(80000)
        ->and($publication->wanted_in_exchange)->toBeNull()
        ->and($publication->images)->toHaveCount(2)
        ->and($publication->images->pluck('position')->all())->toBe([0, 1]);

    foreach ($publication->images as $image) {
        Storage::disk('public')->assertExists($image->path);
        expect($image->path)->toStartWith("publications/{$publication->id}/");
    }
});

test('CreatePublication stores the photos in the requested order', function () {
    $owner = User::factory()->create();
    $a = fakePhoto('a.png', 1);
    $b = fakePhoto('b.png', 2);

    $publication = app(CreatePublication::class)->execute(
        $owner,
        actionData(Category::factory()->leaf()->create()),
        ['new:1', 'new:0'],
        [$a, $b],
    );

    $sizes = $publication->images->map(fn ($image) => Storage::disk('public')->size($image->path))->all();

    expect($sizes)->toBe([2048, 1024]);
});

test('CreatePublication leaves nothing behind when it fails', function () {
    $owner = User::factory()->create();

    expect(fn () => app(CreatePublication::class)->execute(
        $owner,
        actionData(Category::factory()->leaf()->create()),
        ['new:5'],
        [fakePhoto()],
    ))->toThrow(InvalidArgumentException::class);

    expect(Publication::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('UpdatePublication updates the data and keeps the slug', function () {
    $publication = Publication::factory()->sale(10000)->create();
    $slug = $publication->slug;
    $image = $publication->images()->create(['path' => 'publications/x/a.png', 'position' => 0]);

    app(UpdatePublication::class)->execute(
        $publication,
        actionData($publication->category, 'donation'),
        ["existing:{$image->id}"],
        [],
    );

    $publication->refresh();

    expect($publication->title)->toBe('Mesa de centro')
        ->and($publication->modality)->toBe(PublicationModality::Donation)
        ->and($publication->price)->toBeNull()
        ->and($publication->slug)->toBe($slug);
});

test('SyncPublicationImages keeps, reorders, removes and adds in one pass', function () {
    $publication = Publication::factory()->create();
    $paths = [];

    foreach ([0, 1, 2] as $i) {
        $paths[$i] = "publications/{$publication->id}/{$i}.png";
        Storage::disk('public')->put($paths[$i], 'x');
        $publication->images()->create(['path' => $paths[$i], 'position' => $i]);
    }

    $ids = $publication->images()->pluck('id')->all();

    app(SyncPublicationImages::class)->execute(
        $publication,
        ["existing:{$ids[2]}", 'new:0', "existing:{$ids[0]}"],
        [fakePhoto('nueva.webp')],
    );

    $images = $publication->images()->get();

    expect($images)->toHaveCount(3)
        ->and($images[0]->id)->toBe($ids[2])
        ->and($images[2]->id)->toBe($ids[0])
        ->and($images->pluck('position')->all())->toBe([0, 1, 2])
        ->and(PublicationImage::whereKey($ids[1])->exists())->toBeFalse();

    Storage::disk('public')->assertMissing($paths[1]);
    Storage::disk('public')->assertExists($paths[0]);
    Storage::disk('public')->assertExists($images[1]->path);
});

test('SyncPublicationImages refuses images from another publication', function () {
    $publication = Publication::factory()->create();
    $foreign = Publication::factory()->withImages(1)->create()->images()->firstOrFail();

    expect(fn () => app(SyncPublicationImages::class)->execute($publication, ["existing:{$foreign->id}"], []))
        ->toThrow(InvalidArgumentException::class);

    expect(PublicationImage::whereKey($foreign->id)->exists())->toBeTrue();
});

test('SyncPublicationImages rejects malformed tokens', function (string $token) {
    $publication = Publication::factory()->create();

    expect(fn () => app(SyncPublicationImages::class)->execute($publication, [$token], [fakePhoto()]))
        ->toThrow(InvalidArgumentException::class);
})->with(['nuevo:0', 'new:', 'new:-1', 'existing:abc', '', 'new:0;DROP']);

test('UpdatePublicationStatus applies a valid transition', function () {
    $publication = Publication::factory()->sale()->create();

    $result = app(UpdatePublicationStatus::class)->execute($publication, PublicationStatus::Sold);

    expect($result->status)->toBe(PublicationStatus::Sold)
        ->and($publication->refresh()->status)->toBe(PublicationStatus::Sold);
});

test('UpdatePublicationStatus rejects an invalid transition and keeps the status', function () {
    $publication = Publication::factory()->donation()->create();

    expect(fn () => app(UpdatePublicationStatus::class)->execute($publication, PublicationStatus::Sold))
        ->toThrow(InvalidStatusTransition::class, 'No puedes cambiar una publicación de «disponible» a «vendido».');

    expect($publication->refresh()->status)->toBe(PublicationStatus::Available);
});

test('UpdatePublicationStatus cannot leave a final status', function () {
    $publication = Publication::factory()->delivered()->create();

    expect(fn () => app(UpdatePublicationStatus::class)->execute($publication, PublicationStatus::Available))
        ->toThrow(InvalidStatusTransition::class);
});

test('DeletePublication soft deletes and keeps the photos', function () {
    $publication = Publication::factory()->withImages(2)->create();

    app(DeletePublication::class)->execute($publication);

    expect($publication->refresh()->trashed())->toBeTrue()
        ->and($publication->images()->count())->toBe(2);
});
