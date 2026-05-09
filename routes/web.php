<?php

declare(strict_types=1);

use App\Http\Controllers\InvitationEditorController;
use App\Http\Controllers\InvitationPreviewController;
use App\Http\Controllers\PublishedInvitationController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

Route::get('/', fn () => view('welcome'));

// Shared invitation editor + preview. Auth required; InvitationPolicy decides
// whether the user can actually view/update (admin or owner couple).
Route::middleware('auth')->group(function () {
    Route::get('/invitations/{slug}/edit', [InvitationEditorController::class, 'edit'])
        ->where('slug', '[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]')
        ->name('invitations.edit');

    Route::get('/invitations/{slug}/preview', [InvitationPreviewController::class, 'show'])
        ->where('slug', '[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]')
        ->name('invitations.preview');
});

Route::get('/{slug}/{token?}', PublishedInvitationController::class)
    ->where(['token' => '[A-Za-z0-9]{8,16}'])
    ->name('public.invitation');
