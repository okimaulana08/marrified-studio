<?php

declare(strict_types=1);

namespace App\Services\Invitations;

use App\Models\Invitation;
use App\Models\Section;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Orchestrates writes to the Invitation aggregate (the row + future child rows
 * via the per-tab forms in Phase 2+). Phase 1 only exposes `create()` and
 * `delete()` plus a slug uniqueness helper; per-tab updates live on the form
 * objects themselves and call back into here for atomic transactions.
 */
final class InvitationWriter
{
    private const SLUG_REGEX = '/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/';

    /**
     * Section types every invitation gets seeded with on create.
     * Order here = default sort_order on creation. 'story' sits between
     * 'couple' and 'event' for natural narrative flow (Cover → Quotes →
     * Couple → Story → Event → Gallery → Gift → RSVP → Guestbook).
     */
    public const DEFAULT_SECTION_TYPES = [
        'cover', 'quotes', 'couple', 'story', 'event', 'countdown', 'gallery', 'gift', 'rsvp', 'guestbook', 'thanks',
    ];

    /**
     * Create a new invitation with just the basic fields. Other tabs (couple,
     * events, etc.) are filled in afterwards via their own forms.
     *
     * @param  array{slug: string, theme_slug: string, religion_type?: ?string, user_id?: ?int}  $data
     */
    public function create(array $data): Invitation
    {
        $slug = (string) ($data['slug'] ?? '');

        if (! preg_match(self::SLUG_REGEX, $slug)) {
            throw new RuntimeException("Invalid slug format: '{$slug}'. Lowercase alphanumeric + hyphens, 3-50 chars.");
        }

        if (Invitation::query()->where('slug', $slug)->exists()) {
            throw new RuntimeException("Slug '{$slug}' is already taken.");
        }

        return DB::transaction(function () use ($data, $slug) {
            $invitation = Invitation::query()->create([
                'slug' => $slug,
                'theme_slug' => (string) $data['theme_slug'],
                'religion_type' => $data['religion_type'] ?? null,
                'religious_text' => null,
                'user_id' => $data['user_id'] ?? null,
                'customizations' => [],
            ]);

            $this->seedSectionsFromTheme($invitation);

            return $invitation;
        });
    }

    /**
     * Pre-create one row per section type using the theme's default variants.
     * Lets the editor's Sections tab show all toggleable rows without lazy-init
     * elsewhere. Falls back to 'default' when the theme doesn't specify a variant.
     */
    private function seedSectionsFromTheme(Invitation $invitation): void
    {
        $theme = app(ThemeRegistry::class)->find($invitation->theme_slug);
        $defaults = $theme?->defaultSectionVariants ?? [];

        foreach (self::DEFAULT_SECTION_TYPES as $i => $type) {
            Section::query()->create([
                'invitation_id' => $invitation->id,
                'type' => $type,
                'variant' => (string) ($defaults[$type] ?? 'default'),
                'sort_order' => $i,
                'enabled' => true,
            ]);
        }
    }

    /**
     * Suggest a slug from couple names with collision suffixing (-2, -3, …).
     * Pure helper — does not touch the DB beyond uniqueness lookups.
     */
    public function suggestSlug(string $brideName, string $groomName): string
    {
        $base = Str::slug(trim($brideName).'-'.trim($groomName));

        if ($base === '') {
            $base = 'invitation';
        }

        $candidate = $base;
        $i = 2;

        while (Invitation::query()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    public function delete(Invitation $invitation): void
    {
        DB::transaction(fn () => $invitation->delete());
    }
}
