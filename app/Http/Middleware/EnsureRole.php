<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allow only authenticated users whose role matches one of the required roles.
 * Use as `->middleware('role:admin')` or `->middleware('role:admin,couple')`.
 * Unauthenticated users hit the auth middleware first; this only runs after.
 */
final class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $allowed = array_map(fn (string $r) => UserRole::from($r), $roles);

        if (! in_array($user->role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
