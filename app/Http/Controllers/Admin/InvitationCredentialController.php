<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Contracts\View\View;

/**
 * Admin-only credentials management page. The actual issue/regenerate/revoke
 * logic lives in the CredentialManager Livewire component mounted by the view.
 *
 * Route is gated by `auth + role:admin` middleware in routes/admin.php; this
 * controller just resolves the slug and ensures the invitation exists.
 */
final class InvitationCredentialController extends Controller
{
    public function show(string $slug): View
    {
        $invitation = Invitation::query()->where('slug', $slug)->firstOrFail();

        return view('admin.invitations.credentials', ['invitation' => $invitation]);
    }
}
