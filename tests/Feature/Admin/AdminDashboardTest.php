<?php

declare(strict_types=1);

use App\Models\Publication;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the dashboard shows user and publication counters', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create();
    User::factory()->inactive()->create();

    $owner = User::factory()->create();
    Publication::factory()->ownedBy($owner)->count(2)->donation()->create();
    Publication::factory()->ownedBy($owner)->sale()->create();
    Publication::factory()->ownedBy($owner)->exchange()->reserved()->create();
    Publication::factory()->ownedBy($owner)->sold()->hidden()->create();
    Publication::factory()->ownedBy($owner)->create()->delete();

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('stats.users', ['total' => 6, 'active' => 5, 'inactive' => 1, 'admins' => 1])
            ->where('stats.publications.total', 5)
            ->where('stats.publications.hidden', 1)
            ->where('stats.publications.by_status', [
                ['value' => 'available', 'label' => 'Disponible', 'total' => 3],
                ['value' => 'reserved', 'label' => 'Reservado', 'total' => 1],
                ['value' => 'delivered', 'label' => 'Entregado', 'total' => 0],
                ['value' => 'sold', 'label' => 'Vendido', 'total' => 1],
            ])
            ->where('stats.publications.by_modality', [
                ['value' => 'donation', 'label' => 'Donación', 'total' => 2],
                ['value' => 'exchange', 'label' => 'Intercambio', 'total' => 1],
                ['value' => 'sale', 'label' => 'Venta', 'total' => 2],
            ]));
});

test('the dashboard works on an empty platform', function () {
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.publications.total', 0)
            ->where('stats.publications.by_status.0.total', 0));
});

test('every admin route is closed to regular users and guests', function (string $routeName, string $method) {
    $params = match ($routeName) {
        'admin.users.status.update', 'admin.users.role.update' => ['user' => User::factory()->create()],
        'admin.publications.hide', 'admin.publications.unhide' => ['publication' => Publication::factory()->create()],
        default => [],
    };
    $url = route($routeName, $params);

    $this->{$method}($url)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->{$method}($url)->assertForbidden();
})->with([
    ['admin.dashboard', 'get'],
    ['admin.users.index', 'get'],
    ['admin.users.status.update', 'patch'],
    ['admin.users.role.update', 'patch'],
    ['admin.publications.index', 'get'],
    ['admin.publications.hide', 'post'],
    ['admin.publications.unhide', 'delete'],
]);
