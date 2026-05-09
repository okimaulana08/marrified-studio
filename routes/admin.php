<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\InvitationAdminController;
use App\Http\Controllers\Admin\InvitationCredentialController;
use App\Http\Controllers\Admin\MusicAdminController;
use App\Http\Controllers\Admin\ThemeAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::prefix('admin/themes')->name('admin.themes.')->group(function () {
        Route::get('/', [ThemeAdminController::class, 'index'])->name('index');
        Route::get('/create', [ThemeAdminController::class, 'create'])->name('create');
        Route::get('/{slug}/edit', [ThemeAdminController::class, 'edit'])->name('edit');
        Route::get('/{slug}/preview', [ThemeAdminController::class, 'preview'])->name('preview');
        Route::post('/{slug}/clone', [ThemeAdminController::class, 'clone'])->name('clone');
    });

    Route::get('admin/music', [MusicAdminController::class, 'index'])->name('admin.music.index');

    Route::prefix('admin/invitations')->name('admin.invitations.')->group(function () {
        Route::get('/', [InvitationAdminController::class, 'index'])->name('index');
        Route::get('/create', [InvitationAdminController::class, 'create'])->name('create');
        Route::get('/{slug}/credentials', [InvitationCredentialController::class, 'show'])
            ->where('slug', '[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]')
            ->name('credentials');
        Route::delete('/{slug}', [InvitationAdminController::class, 'destroy'])
            ->where('slug', '[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]')
            ->name('destroy');
    });
});
