<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;

/**
 * Authorization for invitation editing & viewing.
 *
 * - Admins can do anything (full access for studio team intervention).
 * - Couples can view/update only the invitation they own (`invitations.user_id`).
 * - Delete is admin-only — couples can't accidentally erase their own data.
 * - List is admin-only — couples don't browse other invitations.
 */
final class InvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return $user->isAdmin() || $invitation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Invitation $invitation): bool
    {
        return $user->isAdmin() || $invitation->user_id === $user->id;
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->isAdmin();
    }
}
