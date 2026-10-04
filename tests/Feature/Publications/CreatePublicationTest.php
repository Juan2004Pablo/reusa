<?php

declare(strict_types=1);

use App\Enums\ItemCondition;
use App\Enums\PublicationModality;
use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->category = Category::factory()->leaf()->create();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function publicationPayload(Category $category, array $overrides = []): array
{
    return array_merge([
        'title' => 'Bicicleta de montaña',
        'description' => 'Bicicleta en buen estado, lista para usar.',
        'category_id' => $category->id,
        'modality' => 'donation',
        'condition' => 'good',
        'location' => 'Laureles, cerca al estadio',
        'images' => [fakePhoto('uno.png')],
        'image_order' => ['new:0'],
    ], $overrides);
}

test('guests cannot see the create form or publish', function () {
    $this->get(route('publications.create'))->assertRedirect(route('login'));
    $this->post(route('publications.store'), publicationPayload($this->category))->assertRedirect(route('login'));

    expect(Publication::count())->toBe(0);
});

test('the create form receives categories and options', function () {
    $macro = Category::factory()->create();
    Category::factory()->leaf($macro)->create();

    $this->actingAs($this->user)->get(route('publications.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('publications/create')
            ->has('categories', 2) // la macro del test más la creada en beforeEach
            ->has('categories.1.children', 1)
            ->where('categories.1.children.0.name', fn ($name) => is_string($name))
            ->has('options.modalities', 3)
            ->has('options.conditions', 3));
});

test('a donation can be published with photos', function () {
    $response = $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'images' => [fakePhoto('uno.png'), fakePhoto('dos.jpg')],
        'image_order' => ['new:0', 'new:1'],
    ]));

    $publication = Publication::with('images')->firstOrFail();

    $response->assertRedirect(route('publications.show', $publication));

    expect($publication->user_id)->toBe($this->user->id)
        ->and($publication->title)->toBe('Bicicleta de montaña')
        ->and($publication->modality)->toBe(PublicationModality::Donation)
        ->and($publication->condition)->toBe(ItemCondition::Good)
        ->and($publication->status)->toBe(PublicationStatus::Available)
        ->and($publication->price)->toBeNull()
        ->and($publication->slug)->toStartWith('bicicleta-de-montana-')
        ->and($publication->images)->toHaveCount(2)
        ->and($publication->images->pluck('position')->all())->toBe([0, 1]);

    foreach ($publication->images as $image) {
        Storage::disk('public')->assertExists($image->path);
    }
});

test('an exchange stores what the owner is looking for', function () {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'modality' => 'exchange',
        'wanted_in_exchange' => 'Libros de cocina',
    ]));

    $publication = Publication::firstOrFail();

    expect($publication->modality)->toBe(PublicationModality::Exchange)
        ->and($publication->wanted_in_exchange)->toBe('Libros de cocina')
        ->and($publication->price)->toBeNull();
});

test('a sale stores the price in COP', function () {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'modality' => 'sale',
        'price' => '120000',
    ]));

    expect(Publication::firstOrFail()->price)->toBe(120000);
});

test('a price written with thousands separators is understood', function () {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'modality' => 'sale',
        'price' => '$ 1.500.000',
    ]));

    expect(Publication::firstOrFail()->price)->toBe(1500000);
});

test('a sale requires a price greater than zero', function (mixed $price) {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'modality' => 'sale',
        'price' => $price,
    ]))->assertSessionHasErrors('price');

    expect(Publication::count())->toBe(0);
})->with([null, '', '0', '-5', 'gratis', '12abc']);

test('the price is dropped when the modality is not a sale', function (string $modality) {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'modality' => $modality,
        'price' => '50000',
    ]))->assertSessionHasNoErrors();

    expect(Publication::firstOrFail()->price)->toBeNull();
})->with(['donation', 'exchange']);

test('what the owner wants in exchange is dropped unless it is an exchange', function (string $modality) {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'modality' => $modality,
        'price' => '10000',
        'wanted_in_exchange' => 'Algo',
    ]));

    expect(Publication::firstOrFail()->wanted_in_exchange)->toBeNull();
})->with(['donation', 'sale']);

test('required fields are validated', function (string $field) {
    $payload = publicationPayload($this->category);
    unset($payload[$field]);

    $this->actingAs($this->user)->post(route('publications.store'), $payload)
        ->assertSessionHasErrors($field);

    expect(Publication::count())->toBe(0);
})->with(['title', 'description', 'category_id', 'modality', 'condition', 'location']);

test('invalid enum values are rejected', function (string $field, string $value) {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [$field => $value]))
        ->assertSessionHasErrors($field);
})->with([['modality', 'barter'], ['condition', 'broken']]);

test('a macro category cannot be used', function () {
    $macro = Category::factory()->create();

    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($macro))
        ->assertSessionHasErrors(['category_id' => 'Selecciona una subcategoría válida.']);
});

test('an unknown category is rejected', function () {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, ['category_id' => 9999]))
        ->assertSessionHasErrors('category_id');
});

test('text fields have a maximum length', function () {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'title' => str_repeat('a', 121),
        'location' => str_repeat('b', 151),
    ]))->assertSessionHasErrors(['title', 'location']);
});

test('at least one photo is required', function () {
    $payload = publicationPayload($this->category);
    unset($payload['images'], $payload['image_order']);

    $this->actingAs($this->user)->post(route('publications.store'), $payload)
        ->assertSessionHasErrors(['image_order' => 'Agrega al menos una fotografía.']);

    expect(Publication::count())->toBe(0);
});

test('no more than four photos are accepted', function () {
    $photos = array_map(fn (int $i) => fakePhoto("foto{$i}.png"), range(1, 5));

    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'images' => $photos,
        'image_order' => ['new:0', 'new:1', 'new:2', 'new:3', 'new:4'],
    ]))->assertSessionHasErrors('image_order');

    expect(Publication::count())->toBe(0);
});

test('four photos are accepted', function () {
    $photos = array_map(fn (int $i) => fakePhoto("foto{$i}.png"), range(1, 4));

    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'images' => $photos,
        'image_order' => ['new:0', 'new:1', 'new:2', 'new:3'],
    ]))->assertSessionHasNoErrors();

    expect(Publication::firstOrFail()->images)->toHaveCount(4);
});

test('photos must be jpg, png or webp', function (string $name) {
    $file = UploadedFile::fake()->createWithContent($name, 'contenido que no es una imagen');

    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'images' => [$file],
        'image_order' => ['new:0'],
    ]))->assertSessionHasErrors('images.0');

    expect(Publication::count())->toBe(0);
})->with(['documento.pdf', 'animacion.gif', 'vector.svg', 'script.php']);

test('a file renamed to .png is rejected by its real content', function () {
    $path = tempnam(sys_get_temp_dir(), 'fake');
    file_put_contents($path, '<?php echo "no soy una imagen";');
    $file = new UploadedFile($path, 'falso.png', 'image/png', null, true);

    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'images' => [$file],
        'image_order' => ['new:0'],
    ]))->assertSessionHasErrors('images.0');

    expect(Publication::count())->toBe(0);
});

test('jpg, jpeg, png and webp photos are accepted', function (string $name) {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'images' => [fakePhoto($name)],
        'image_order' => ['new:0'],
    ]))->assertSessionHasNoErrors();

    expect(Publication::count())->toBe(1);
})->with(['a.jpg', 'a.jpeg', 'a.png', 'a.webp']);

test('a photo larger than 2 MB is rejected', function () {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'images' => [fakePhoto('grande.png', 2049)],
        'image_order' => ['new:0'],
    ]))->assertSessionHasErrors('images.0');

    expect(Publication::count())->toBe(0);
});

test('a photo of exactly 2 MB is accepted', function () {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'images' => [fakePhoto('limite.png', 2048)],
        'image_order' => ['new:0'],
    ]))->assertSessionHasNoErrors();

    expect(Publication::count())->toBe(1);
});

test('the photo order must match the uploaded files', function (array $order) {
    $response = $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, [
        'images' => [fakePhoto('uno.png')],
        'image_order' => $order,
    ]));

    $response->assertSessionHasErrors();
    expect(collect($response->baseResponse->getSession()->get('errors')->getBag('default')->keys())
        ->contains(fn (string $key) => str_starts_with($key, 'image_order')))->toBeTrue()
        ->and(Publication::count())->toBe(0);
})->with([
    'index out of range' => [['new:3']],
    'file referenced twice' => [['new:0', 'new:0']],
    'existing image on a new publication' => [['existing:1']],
    'garbage token' => [['../../etc/passwd']],
]);

test('nothing is stored on disk when validation fails', function () {
    $this->actingAs($this->user)->post(route('publications.store'), publicationPayload($this->category, ['title' => '']));

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('an inactive user cannot publish', function () {
    $inactive = User::factory()->inactive()->create();

    $this->actingAs($inactive)->post(route('publications.store'), publicationPayload($this->category))
        ->assertRedirect(route('login'));

    expect(Publication::count())->toBe(0);
});
