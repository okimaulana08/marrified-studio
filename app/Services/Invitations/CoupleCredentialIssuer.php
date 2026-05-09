<?php

declare(strict_types=1);

namespace App\Services\Invitations;

use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Admin-controlled credential lifecycle for couple users.
 *
 * Issue:
 *   - Creates a new User row with role=couple and a random plaintext password.
 *   - Links it to the invitation via invitations.user_id.
 *   - Returns the plaintext password ONCE so admin can copy & share. After that
 *     it's never recoverable — only re-generatable.
 *
 * Regenerate:
 *   - Replaces the linked user's password with a fresh random one.
 *   - Use when couple loses their credentials.
 *
 * Revoke:
 *   - Deletes the linked user (FK ON DELETE SET NULL keeps invitation alive).
 */
final class CoupleCredentialIssuer
{
    private const PASSWORD_LENGTH = 12;

    /**
     * @return array{user: User, plaintext_password: string}
     */
    public function issue(Invitation $invitation, string $email): array
    {
        $email = strtolower(trim($email));

        if ($email === '') {
            throw new RuntimeException('Email wajib diisi.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Email tidak valid: '{$email}'.");
        }

        if ($invitation->user_id !== null) {
            throw new RuntimeException('Invitation sudah punya kredensial. Pakai regenerate untuk reset password.');
        }

        if (User::query()->where('email', $email)->exists()) {
            throw new RuntimeException("Email '{$email}' sudah dipakai user lain.");
        }

        $plaintext = $this->generatePassword();

        return DB::transaction(function () use ($invitation, $email, $plaintext) {
            $coupleName = $this->coupleName($invitation);
            $user = User::query()->create([
                'name' => $coupleName,
                'email' => $email,
                'password' => Hash::make($plaintext),
                'role' => UserRole::Couple,
            ]);

            $invitation->update(['user_id' => $user->id]);

            return ['user' => $user, 'plaintext_password' => $plaintext];
        });
    }

    /**
     * @return array{user: User, plaintext_password: string}
     */
    public function regenerate(Invitation $invitation): array
    {
        $user = $this->resolveLinkedUser($invitation);

        $plaintext = $this->generatePassword();

        return DB::transaction(function () use ($user, $plaintext) {
            $user->update(['password' => Hash::make($plaintext)]);

            return ['user' => $user->fresh(), 'plaintext_password' => $plaintext];
        });
    }

    public function revoke(Invitation $invitation): void
    {
        $user = $this->resolveLinkedUser($invitation);

        DB::transaction(function () use ($user) {
            // FK ON DELETE SET NULL on invitations.user_id nulls the link
            // automatically. No need to update invitation explicitly.
            $user->delete();
        });
    }

    private function resolveLinkedUser(Invitation $invitation): User
    {
        if ($invitation->user_id === null) {
            throw new RuntimeException('Invitation belum punya kredensial. Pakai issue dulu.');
        }

        $user = User::query()->find($invitation->user_id);

        if ($user === null || ! $user->isCouple()) {
            throw new RuntimeException('Linked user tidak ditemukan atau bukan role couple.');
        }

        return $user;
    }

    /**
     * 12-char alphanumeric (Str::random returns mixed-case + digits).
     * No symbols to avoid copy-paste ambiguity.
     */
    private function generatePassword(): string
    {
        return Str::random(self::PASSWORD_LENGTH);
    }

    private function coupleName(Invitation $invitation): string
    {
        $invitation->loadMissing('couple');
        $couple = $invitation->couple;

        if ($couple === null) {
            return $invitation->slug;
        }

        $bride = trim((string) ($couple->bride_nickname ?: $couple->bride_name));
        $groom = trim((string) ($couple->groom_nickname ?: $couple->groom_name));

        $parts = array_filter([$bride, $groom]);

        return $parts === [] ? $invitation->slug : implode(' & ', $parts);
    }
}
