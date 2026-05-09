<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

/**
 * Single source of truth for "where does this user go after login?".
 * Couples without an invitation row land on dashboard with a notice; with one,
 * they go straight to their editor. Admins always go to the invitations list.
 */
final class AuthRedirector
{
    public static function redirectFor(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.invitations.index'));
        }

        $invitation = $user->invitation()->first();

        if ($invitation === null) {
            return redirect()->route('dashboard');
        }

        return redirect()->intended(route('invitations.edit', $invitation->slug));
    }
}
