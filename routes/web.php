<?php

declare(strict_types=1);

use App\Http\Controllers\PublishedInvitationController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/admin.php';

Route::get('/', fn () => view('welcome'));

Route::get('/{slug}/{token?}', PublishedInvitationController::class)
    ->where(['token' => '[A-Za-z0-9]{8,16}'])
    ->name('public.invitation');
