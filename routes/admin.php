<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ThemeAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/themes')->name('admin.themes.')->group(function () {
    Route::get('/', [ThemeAdminController::class, 'index'])->name('index');
    Route::get('/create', [ThemeAdminController::class, 'create'])->name('create');
    Route::get('/{slug}/edit', [ThemeAdminController::class, 'edit'])->name('edit');
    Route::get('/{slug}/preview', [ThemeAdminController::class, 'preview'])->name('preview');
    Route::post('/{slug}/clone', [ThemeAdminController::class, 'clone'])->name('clone');
});
