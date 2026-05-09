<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

/**
 * Shared editor route used by BOTH admin and the owning couple. Authorization
 * is delegated to InvitationPolicy::update — admin always allowed, couple
 * only on their own invitation. Phase 1: stub view; Phase 2 mounts the
 * InvitationEditor Livewire component here.
 */
final class InvitationEditorController extends Controller
{
    public function edit(string $slug): View
    {
        $invitation = Invitation::query()->where('slug', $slug)->firstOrFail();
        Gate::authorize('update', $invitation);

        return view('invitations.editor', ['invitation' => $invitation]);
    }
}
