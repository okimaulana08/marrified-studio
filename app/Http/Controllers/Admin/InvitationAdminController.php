<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\Invitations\InvitationWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Admin-only management of invitations: list + create + delete + clone.
 * Tabs/edit live at the shared route `/invitations/{slug}/edit` so couples
 * use the same editor; this controller only handles the management surface.
 *
 * Phase 1: stub views. Phase 2 wires the Livewire components in.
 */
final class InvitationAdminController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Invitation::class);

        return view('admin.invitations.index');
    }

    public function create(): View
    {
        Gate::authorize('create', Invitation::class);

        return view('admin.invitations.create');
    }

    public function destroy(string $slug, InvitationWriter $writer): RedirectResponse
    {
        $invitation = Invitation::query()->where('slug', $slug)->firstOrFail();
        Gate::authorize('delete', $invitation);

        $writer->delete($invitation);

        return redirect()
            ->route('admin.invitations.index')
            ->with('flash_message', "Invitation '{$slug}' dihapus.")
            ->with('flash_type', 'success');
    }
}
