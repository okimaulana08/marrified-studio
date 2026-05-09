<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Admin music library page. Mounts the MusicLibraryManager Livewire
 * component which handles upload + listing + deletion of MP3 tracks.
 *
 * Authorization: route is gated by `auth + role:admin` middleware in
 * routes/admin.php; couple users never reach here.
 */
final class MusicAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.music.index');
    }
}
