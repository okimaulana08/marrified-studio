<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

/**
 * Renders the public invitation layout with a synthetic guest so iframe
 * previews don't increment opens_count or pollute first/last_opened_at.
 * Used by the live preview pane in the editor.
 */
final class InvitationPreviewController extends Controller
{
    public function show(string $slug): View
    {
        $invitation = Invitation::query()
            ->where('slug', $slug)
            ->with(['couple', 'events', 'sections', 'giftAccounts', 'musicTrack'])
            ->firstOrFail();

        Gate::authorize('view', $invitation);

        $theme = app(ThemeRegistry::class)->find($invitation->theme_slug)
            ?? abort(404, "Theme '{$invitation->theme_slug}' missing for preview.");

        // Synthetic guest — never persisted; lets cover/greeting render with
        // a placeholder name so designers see the bound layout.
        $guest = new Guest([
            'name' => 'Preview Guest',
            'relation' => 'Bapak/Ibu',
            'phone' => null,
        ]);
        $guest->id = 0;
        $guest->invitation_id = $invitation->id;
        $guest->token = 'PREVIEW0';

        return view('layouts.render', [
            'invitation' => $invitation,
            'theme' => $theme,
            'guest' => $guest,
        ]);
    }
}
