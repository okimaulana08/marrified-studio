<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\Section;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the new 'story' section type to existing invitations that were created
 * before this section type was added to InvitationWriter::DEFAULT_SECTION_TYPES.
 *
 * Idempotent: only inserts when missing. New invitations created post-this
 * already include 'story' via the seedSectionsFromTheme() loop.
 */
return new class extends Migration
{
    public function up(): void
    {
        $registry = app(ThemeRegistry::class);

        Invitation::query()->lazyById()->each(function (Invitation $invitation) use ($registry) {
            $exists = Section::query()
                ->where('invitation_id', $invitation->id)
                ->where('type', 'story')
                ->exists();

            if ($exists) {
                return;
            }

            $theme = $registry->find($invitation->theme_slug);
            $variant = (string) ($theme?->defaultSectionVariants['story'] ?? 'default');

            $maxOrder = (int) Section::query()
                ->where('invitation_id', $invitation->id)
                ->max('sort_order');

            Section::query()->create([
                'invitation_id' => $invitation->id,
                'type' => 'story',
                'variant' => $variant,
                'sort_order' => $maxOrder + 1,
                'enabled' => true,
                'content' => null,
            ]);
        });
    }

    public function down(): void
    {
        Section::query()->where('type', 'story')->delete();
    }
};
