<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PublicationController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status.update');
        Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role.update');

        Route::get('publications', [PublicationController::class, 'index'])->name('publications.index');
        Route::post('publications/{publication:slug}/hide', [PublicationController::class, 'hide'])->name('publications.hide');
        Route::delete('publications/{publication:slug}/hide', [PublicationController::class, 'unhide'])->name('publications.unhide');
    });
