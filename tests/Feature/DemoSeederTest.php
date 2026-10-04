<?php

declare(strict_types=1);

use App\Enums\PublicationModality;
use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Publication;
use App\Models\PublicationImage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoPublicationSeeder;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('the database seeder creates categories, users and demo publications', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Category::count())->toBe(38)
        ->and(User::where('role', UserRole::Admin)->count())->toBe(1)
        ->and(User::where('role', UserRole::User)->count())->toBe(5)
        ->and(Publication::count())->toBeGreaterThanOrEqual(20);
});

test('the admin demo account exists and can log in', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@reusa.test')->firstOrFail();

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->is_active)->toBeTrue();

    $this->post(route('login.store'), ['email' => 'admin@reusa.test', 'password' => 'password']);
    $this->assertAuthenticatedAs($admin);
});

test('demo publications cover every modality and status', function () {
    $this->seed(DatabaseSeeder::class);

    foreach (PublicationModality::cases() as $modality) {
        expect(Publication::where('modality', $modality)->count())->toBeGreaterThan(0, $modality->value);
    }

    foreach (PublicationStatus::cases() as $status) {
        expect(Publication::where('status', $status)->count())->toBeGreaterThan(0, $status->value);
    }

    expect(Publication::whereNotNull('hidden_at')->count())->toBeGreaterThanOrEqual(1)
        ->and(Publication::whereNotNull('hidden_at')->whereNotNull('hidden_reason')->count())->toBeGreaterThanOrEqual(1);
});

test('demo publications respect the business rules', function () {
    $this->seed(DatabaseSeeder::class);

    Publication::with('category')->get()->each(function (Publication $publication) {
        expect($publication->category->isLeaf())->toBeTrue();

        if ($publication->modality === PublicationModality::Sale) {
            expect($publication->price)->toBeGreaterThan(0);
        } else {
            expect($publication->price)->toBeNull();
        }

        if ($publication->status === PublicationStatus::Sold) {
            expect($publication->modality)->toBe(PublicationModality::Sale);
        }

        expect($publication->status === PublicationStatus::Delivered && $publication->modality === PublicationModality::Sale)->toBeFalse();
    });
});

test('demo photos are real image files on the public disk', function () {
    $this->seed(DatabaseSeeder::class);

    $images = PublicationImage::all();

    expect($images->count())->toBeGreaterThan(20)
        ->and(Publication::doesntHave('images')->count())->toBeGreaterThanOrEqual(1)
        ->and(Publication::withCount('images')->get()->max('images_count'))->toBeLessThanOrEqual(4);

    foreach ($images as $image) {
        Storage::disk('public')->assertExists($image->path);
    }
});

test('seeding twice does not duplicate the demo publications', function () {
    $this->seed(DatabaseSeeder::class);
    $count = Publication::count();

    $this->seed(DemoPublicationSeeder::class);

    expect(Publication::count())->toBe($count);
});

test('demo data is not seeded in production', function () {
    $this->app['env'] = 'production';

    // Se invoca el seeder directamente: `db:seed` pediría confirmación interactiva en producción.
    app(DatabaseSeeder::class)->__invoke();

    expect(Category::count())->toBe(38)
        ->and(User::count())->toBe(0)
        ->and(Publication::count())->toBe(0);
});

test('the placeholder generator produces valid, small PNG files', function () {
    $png = PlaceholderImage::png(120, 90, [200, 220, 255], [20, 40, 120], 2);
    $info = getimagesizefromstring($png);

    expect($info)->not->toBeFalse()
        ->and($info[0])->toBe(120)
        ->and($info[1])->toBe(90)
        ->and($info['mime'])->toBe('image/png')
        ->and(strlen($png))->toBeLessThan(20000);
});

test('different variants produce different images', function () {
    $a = PlaceholderImage::png(60, 40, [255, 255, 255], [0, 0, 0], 1);
    $b = PlaceholderImage::png(60, 40, [255, 255, 255], [0, 0, 0], 2);

    expect($a)->not->toBe($b);
});
