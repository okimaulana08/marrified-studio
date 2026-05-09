<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\AuthRedirector;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Post-login landing. Mostly redirects to the right place based on role and
 * whether the couple has an invitation. Renders the empty-state page only
 * when a couple-user has been created but no invitation has been linked yet.
 */
final class DashboardController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->invitation()->exists()) {
            return AuthRedirector::redirectFor($user);
        }

        return view('auth.no-invitation', ['user' => $user]);
    }
}
