<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Testing\TestResponse;

/**
 * @return list<string>
 */
function catalogTitles(TestResponse $response): array
{
    return collect($response->inertiaProps('publications.data'))->pluck('title')->all();
}

/**
 * @param  array<string, mixed>  $query
 * @return list<string>
 */
function catalog(mixed $test, array $query = []): array
{
    return catalogTitles($test->get(route('publications.index', $query))->assertOk());
}

test('the catalog is public and renders the publications page', function () {
    Publication::factory()->count(3)->create();

    $this->get(route('publications.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('publications/index')
            ->has('publications.data', 3)
            ->has('categories')
            ->has('options.modalities', 3)
            ->has('options.statuses', 4)
            ->has('options.sorts', 3));
});

test('by default only available and visible publications are listed', function () {
    Publication::factory()->create(['title' => 'Disponible']);
    Publication::factory()->reserved()->create(['title' => 'Reservada']);
    Publication::factory()->delivered()->create(['title' => 'Entregada']);
    Publication::factory()->sold()->create(['title' => 'Vendida']);
    Publication::factory()->hidden()->create(['title' => 'Oculta']);
    Publication::factory()->create(['title' => 'Borrada'])->delete();

    expect(catalog($this))->toBe(['Disponible']);
});

test('hidden publications never appear in the catalog, whatever the status filter', function () {
    Publication::factory()->hidden('Spam')->create(['title' => 'Oculta']);
    Publication::factory()->create(['title' => 'Visible']);

    expect(catalog($this, ['status' => 'all']))->toBe(['Visible']);
});

test('the status filter shows the requested status', function (string $status, string $expected) {
    Publication::factory()->create(['title' => 'Disponible']);
    Publication::factory()->reserved()->create(['title' => 'Reservada']);
    Publication::factory()->delivered()->create(['title' => 'Entregada']);
    Publication::factory()->sold()->create(['title' => 'Vendida']);

    expect(catalog($this, ['status' => $status]))->toBe([$expected]);
})->with([
    ['available', 'Disponible'],
    ['reserved', 'Reservada'],
    ['delivered', 'Entregada'],
    ['sold', 'Vendida'],
]);

test('status=all lists every visible status', function () {
    Publication::factory()->create(['title' => 'A']);
    Publication::factory()->reserved()->create(['title' => 'B']);
    Publication::factory()->delivered()->create(['title' => 'C']);
    Publication::factory()->sold()->create(['title' => 'D']);

    expect(catalog($this, ['status' => 'all']))->toHaveCount(4);
});

test('the modality filter works', function (string $modality, string $expected) {
    Publication::factory()->donation()->create(['title' => 'Regalo']);
    Publication::factory()->exchange()->create(['title' => 'Trueque']);
    Publication::factory()->sale()->create(['title' => 'Venta']);

    expect(catalog($this, ['modality' => $modality]))->toBe([$expected]);
})->with([['donation', 'Regalo'], ['exchange', 'Trueque'], ['sale', 'Venta']]);

test('the macro category filter includes all its micro categories', function () {
    $this->seed(CategorySeeder::class);
    $clothes = Category::where('slug', 'ropa-calzado-y-accesorios')->firstOrFail();
    $shoes = Category::where('slug', 'calzado')->firstOrFail();
    $women = Category::where('slug', 'ropa-de-mujer')->firstOrFail();
    $books = Category::where('slug', 'libros-de-texto')->firstOrFail();

    Publication::factory()->inCategory($shoes)->create(['title' => 'Zapatos']);
    Publication::factory()->inCategory($women)->create(['title' => 'Vestido']);
    Publication::factory()->inCategory($books)->create(['title' => 'Libro']);

    expect(catalog($this, ['category' => $clothes->slug]))->toEqualCanonicalizing(['Zapatos', 'Vestido']);
});

test('the micro category filter narrows the results', function () {
    $this->seed(CategorySeeder::class);
    $shoes = Category::where('slug', 'calzado')->firstOrFail();
    $women = Category::where('slug', 'ropa-de-mujer')->firstOrFail();

    Publication::factory()->inCategory($shoes)->create(['title' => 'Zapatos']);
    Publication::factory()->inCategory($women)->create(['title' => 'Vestido']);

    expect(catalog($this, ['subcategory' => 'calzado']))->toBe(['Zapatos'])
        ->and(catalog($this, ['category' => 'ropa-calzado-y-accesorios', 'subcategory' => 'calzado']))->toBe(['Zapatos']);
});

test('a micro category that does not belong to the macro category yields nothing', function () {
    $this->seed(CategorySeeder::class);
    Publication::factory()->inCategory(Category::where('slug', 'calzado')->firstOrFail())->create();

    expect(catalog($this, ['category' => 'libros-y-material-educativo', 'subcategory' => 'calzado']))->toBe([]);
});

test('an unknown category yields no results instead of failing', function () {
    Publication::factory()->create();

    expect(catalog($this, ['category' => 'no-existe']))->toBe([]);
});

test('text search matches title and description, ignoring case', function () {
    Publication::factory()->create(['title' => 'Bicicleta rodado 26', 'description' => 'Para pasear por la ciudad']);
    Publication::factory()->create(['title' => 'Mesa de comedor', 'description' => 'Madera, sirve como BICICLETERO improvisado']);
    Publication::factory()->create(['title' => 'Libro', 'description' => 'Novela']);

    expect(catalog($this, ['q' => 'BICICLETA']))->toBe(['Bicicleta rodado 26'])
        ->and(catalog($this, ['q' => 'bicicletero']))->toBe(['Mesa de comedor'])
        ->and(catalog($this, ['q' => 'pasear']))->toBe(['Bicicleta rodado 26']);
});

test('every word of the search must match', function () {
    Publication::factory()->create(['title' => 'Bicicleta roja', 'description' => 'Con canasta']);
    Publication::factory()->create(['title' => 'Bicicleta azul', 'description' => 'Sin canasta']);

    expect(catalog($this, ['q' => 'bicicleta roja']))->toBe(['Bicicleta roja'])
        ->and(catalog($this, ['q' => 'canasta bicicleta']))->toHaveCount(2);
});

test('search wildcards are treated literally', function () {
    Publication::factory()->create(['title' => 'Oferta 50% descuento']);
    Publication::factory()->create(['title' => 'Oferta de verano']);
    Publication::factory()->create(['title' => 'Tabla_madera']);
    Publication::factory()->create(['title' => 'Tabla de madera']);

    expect(catalog($this, ['q' => '50%']))->toBe(['Oferta 50% descuento'])
        ->and(catalog($this, ['q' => '%']))->toBe(['Oferta 50% descuento'])
        ->and(catalog($this, ['q' => 'Tabla_madera']))->toBe(['Tabla_madera']);
});

test('search is safe against SQL injection attempts', function () {
    Publication::factory()->count(2)->create();

    expect(catalog($this, ['q' => "' OR 1=1 --"]))->toBe([])
        ->and(Publication::count())->toBe(2);
});

test('sorting by most recent is the default', function () {
    Publication::factory()->create(['title' => 'Vieja', 'created_at' => now()->subDays(5)]);
    Publication::factory()->create(['title' => 'Nueva', 'created_at' => now()]);
    Publication::factory()->create(['title' => 'Media', 'created_at' => now()->subDays(2)]);

    expect(catalog($this))->toBe(['Nueva', 'Media', 'Vieja'])
        ->and(catalog($this, ['sort' => 'recent']))->toBe(['Nueva', 'Media', 'Vieja']);
});

test('sorting by price puts items without a price last', function () {
    Publication::factory()->sale(30000)->create(['title' => 'Media']);
    Publication::factory()->sale(10000)->create(['title' => 'Barata']);
    Publication::factory()->sale(90000)->create(['title' => 'Cara']);
    Publication::factory()->donation()->create(['title' => 'Regalo']);

    expect(catalog($this, ['sort' => 'price_asc']))->toBe(['Barata', 'Media', 'Cara', 'Regalo'])
        ->and(catalog($this, ['sort' => 'price_desc']))->toBe(['Cara', 'Media', 'Barata', 'Regalo']);
});

test('filters can be combined', function () {
    $this->seed(CategorySeeder::class);
    $bikes = Category::where('slug', 'bicicletas-y-accesorios')->firstOrFail();
    $tools = Category::where('slug', 'herramientas-manuales')->firstOrFail();

    Publication::factory()->inCategory($bikes)->sale(200000)->create(['title' => 'Bici cara']);
    Publication::factory()->inCategory($bikes)->sale(80000)->create(['title' => 'Bici barata']);
    Publication::factory()->inCategory($bikes)->donation()->create(['title' => 'Bici regalo']);
    Publication::factory()->inCategory($bikes)->sale(50000)->reserved()->create(['title' => 'Bici reservada']);
    Publication::factory()->inCategory($tools)->sale(60000)->create(['title' => 'Martillo bici']);

    $titles = catalog($this, [
        'q' => 'bici',
        'category' => 'deportes-y-tiempo-libre',
        'modality' => 'sale',
        'status' => 'available',
        'sort' => 'price_asc',
    ]);

    expect($titles)->toBe(['Bici barata', 'Bici cara']);
});

test('the current filters are echoed back for the interface', function () {
    $this->get(route('publications.index', ['q' => 'mesa', 'modality' => 'sale', 'sort' => 'price_asc']))
        ->assertInertia(fn ($page) => $page
            ->where('filters.q', 'mesa')
            ->where('filters.modality', 'sale')
            ->where('filters.sort', 'price_asc')
            ->where('filters.status', 'available')
            ->where('filters.category', ''));
});

test('invalid filter values are ignored instead of failing', function () {
    Publication::factory()->count(2)->create();

    $this->get(route('publications.index', ['modality' => 'barter', 'status' => 'archived', 'sort' => 'magic', 'page' => 'x', 'q' => ['array']]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('publications.data', 2)
            ->where('filters.modality', '')
            ->where('filters.status', 'available')
            ->where('filters.sort', 'recent'));
});

test('pagination shows 12 per page and keeps the filters in its links', function () {
    Publication::factory()->count(14)->sale(1000)->create();
    Publication::factory()->count(3)->donation()->create();

    $first = $this->get(route('publications.index', ['modality' => 'sale', 'sort' => 'price_asc']))->assertOk();

    expect($first->inertiaProps('publications.data'))->toHaveCount(12)
        ->and($first->inertiaProps('publications.meta.total'))->toBe(14)
        ->and($first->inertiaProps('publications.meta.last_page'))->toBe(2);

    $next = $first->inertiaProps('publications.links.next');

    expect($next)->toContain('modality=sale')->toContain('sort=price_asc')->toContain('page=2');

    $second = $this->get($next)->assertOk();

    expect($second->inertiaProps('publications.data'))->toHaveCount(2)
        ->and($second->inertiaProps('filters.modality'))->toBe('sale');
});

test('cards expose only the data needed to display them', function () {
    $owner = User::factory()->withContact()->create();
    Publication::factory()->ownedBy($owner)->sale(45000)->withImages(2)->create();

    $card = $this->get(route('publications.index'))->inertiaProps('publications.data.0');

    expect($card)->toHaveKeys(['id', 'slug', 'title', 'modality', 'status', 'price', 'location', 'category', 'cover_url', 'created_at'])
        ->and($card)->not->toHaveKeys(['description', 'user', 'user_id', 'email', 'phone'])
        ->and($card['price'])->toBe(45000)
        ->and($card['modality']['label'])->toBe('Venta')
        ->and($card['cover_url'])->toStartWith('/storage/');
});

test('a card without photos has no cover', function () {
    Publication::factory()->create();

    expect($this->get(route('publications.index'))->inertiaProps('publications.data.0.cover_url'))->toBeNull();
});
