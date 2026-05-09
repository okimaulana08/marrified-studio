<?php

declare(strict_types=1);

namespace App\Services\Invitations;

use App\Models\Couple;
use App\Models\Guest;
use App\Models\Invitation;
use App\Support\GuestToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Deep-copies an invitation to a new slug. Useful when admin wants to seed
 * a new client from a similar previous one (same theme, same sections layout)
 * without re-doing the whole skeleton.
 *
 * What gets copied:
 *   - invitation row (slug overridden, user_id reset, customizations + religious_text carried)
 *   - couple (1:1)
 *   - events (1:many)
 *   - sections (1:many with same variants + enabled + sort_order)
 *   - gift_accounts (1:many)
 *   - guests (1:many WITH FRESH TOKENS — opens_count resets to 0)
 *   - media: invitations/{old_id}/* directory → invitations/{new_id}/*
 *
 * What does NOT get copied:
 *   - user_id link (admin must re-issue credentials for the clone)
 *   - guestbook_messages, rsvps (those are guest-side history of the original)
 *   - opens_count, first_opened_at, last_opened_at on guests (fresh slate)
 */
final class InvitationCloner
{
    private const SLUG_REGEX = '/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/';

    private const MEDIA_DISK = 'invitation_media';

    public function clone(Invitation $source, string $targetSlug): Invitation
    {
        if (! preg_match(self::SLUG_REGEX, $targetSlug)) {
            throw new RuntimeException("Invalid slug format: '{$targetSlug}'.");
        }

        if ($source->slug === $targetSlug) {
            throw new RuntimeException('Slug tujuan harus berbeda dari sumber.');
        }

        if (Invitation::query()->where('slug', $targetSlug)->exists()) {
            throw new RuntimeException("Slug '{$targetSlug}' is already taken.");
        }

        $source->loadMissing(['couple', 'events', 'sections', 'giftAccounts', 'guests']);

        return DB::transaction(function () use ($source, $targetSlug) {
            $clone = Invitation::query()->create([
                'slug' => $targetSlug,
                'user_id' => null,
                'theme_slug' => $source->theme_slug,
                'religion_type' => $source->religion_type,
                'religious_text' => $source->religious_text,
                'customizations' => $source->customizations ?? [],
            ]);

            $this->copyCouple($source, $clone);
            $this->copyEvents($source, $clone);
            $this->copySections($source, $clone);
            $this->copyGiftAccounts($source, $clone);
            $this->copyGuests($source, $clone);
            $this->copyMedia($source, $clone);

            return $clone->fresh(['couple', 'events', 'sections', 'giftAccounts', 'guests']);
        });
    }

    private function copyCouple(Invitation $source, Invitation $clone): void
    {
        if ($source->couple === null) {
            return;
        }

        $couple = $source->couple->replicate(['id', 'created_at', 'updated_at']);
        $couple->invitation_id = $clone->id;

        // Photo paths still reference source invitation_id; fix them after
        // copyMedia() has duplicated the files.
        if ($source->couple->bride_photo_path !== null) {
            $couple->bride_photo_path = str_replace(
                "{$source->id}/",
                "{$clone->id}/",
                $source->couple->bride_photo_path,
            );
        }
        if ($source->couple->groom_photo_path !== null) {
            $couple->groom_photo_path = str_replace(
                "{$source->id}/",
                "{$clone->id}/",
                $source->couple->groom_photo_path,
            );
        }

        $couple->save();
    }

    private function copyEvents(Invitation $source, Invitation $clone): void
    {
        foreach ($source->events as $event) {
            $copy = $event->replicate(['id', 'created_at', 'updated_at']);
            $copy->invitation_id = $clone->id;
            $copy->save();
        }
    }

    private function copySections(Invitation $source, Invitation $clone): void
    {
        foreach ($source->sections as $section) {
            $copy = $section->replicate(['id', 'created_at', 'updated_at']);
            $copy->invitation_id = $clone->id;
            $copy->save();
        }
    }

    private function copyGiftAccounts(Invitation $source, Invitation $clone): void
    {
        foreach ($source->giftAccounts as $account) {
            $copy = $account->replicate(['id', 'created_at', 'updated_at']);
            $copy->invitation_id = $clone->id;
            $copy->save();
        }
    }

    /**
     * Each guest gets a fresh unique token; opens_count + opened_at columns
     * are reset so the cloned invitation starts with clean analytics.
     */
    private function copyGuests(Invitation $source, Invitation $clone): void
    {
        foreach ($source->guests as $guest) {
            Guest::query()->create([
                'invitation_id' => $clone->id,
                'name' => $guest->name,
                'relation' => $guest->relation,
                'phone' => $guest->phone,
                'token' => GuestToken::ensureUnique(),
                'opens_count' => 0,
                'first_opened_at' => null,
                'last_opened_at' => null,
            ]);
        }
    }

    /**
     * Filesystem-only: duplicate the entire `invitations/{source_id}` tree
     * into `invitations/{clone_id}` on the invitation_media disk. Skipped
     * silently when the source has no media at all.
     */
    private function copyMedia(Invitation $source, Invitation $clone): void
    {
        $disk = Storage::disk(self::MEDIA_DISK);
        $sourceDir = (string) $source->id;
        $cloneDir = (string) $clone->id;

        if (! $disk->directoryExists($sourceDir)) {
            return;
        }

        foreach ($disk->allFiles($sourceDir) as $file) {
            $relative = ltrim(substr($file, strlen($sourceDir)), '/');
            $disk->put("{$cloneDir}/{$relative}", $disk->get($file));
        }
    }
}
