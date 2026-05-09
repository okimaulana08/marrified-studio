<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// GET /login is open: controller redirects authenticated users via AuthRedirector
// (so admins land on /admin/invitations, not the default 'guest'-middleware fallback).
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::middleware('guest')->post('/login', [LoginController::class, 'store']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
});
