<?php

declare(strict_types=1);

use App\Http\Controllers\MyPublicationController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\PublicationStatusController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::inertia('terms', 'terms')->name('terms');

Route::get('publications', [PublicationController::class, 'index'])->name('publications.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // `create` debe declararse antes que `{publication}` para no confundirse con un slug.
    Route::get('publications/create', [PublicationController::class, 'create'])->name('publications.create');
    Route::post('publications', [PublicationController::class, 'store'])->name('publications.store');
    Route::get('publications/{publication:slug}/edit', [PublicationController::class, 'edit'])->name('publications.edit');
    Route::put('publications/{publication:slug}', [PublicationController::class, 'update'])->name('publications.update');
    Route::patch('publications/{publication:slug}/status', [PublicationStatusController::class, 'update'])->name('publications.status.update');
    Route::delete('publications/{publication:slug}', [PublicationController::class, 'destroy'])->name('publications.destroy');

    Route::get('my/publications', MyPublicationController::class)->name('my-publications.index');
});

Route::get('publications/{publication:slug}', [PublicationController::class, 'show'])->name('publications.show');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
