<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Enums\ReligionType;
use App\Models\Invitation;
use Livewire\Attributes\Validate;
use Livewire\Form;

/**
 * Identity + theme + religion of an invitation. Slug & theme are immutable
 * after first save (couple-side editor renders them read-only via $isAdmin).
 */
final class InvitationBasicForm extends Form
{
    #[Validate('required|regex:/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/')]
    public string $slug = '';

    #[Validate('required|string|max:64')]
    public string $themeSlug = '';

    #[Validate('required|in:islam,christian,catholic,hindu,buddhist,none')]
    public string $religionType = ReligionType::Islam->value;

    public function fillFromModel(Invitation $invitation): void
    {
        $this->slug = $invitation->slug;
        $this->themeSlug = $invitation->theme_slug;
        $this->religionType = $invitation->religion_type ?? ReligionType::None->value;
    }

    /**
     * Returns attributes ready for InvitationWriter::create() or for updating
     * editable fields (only religion_type is editable post-create — slug and
     * theme_slug stay locked for URL stability and brand consistency).
     *
     * @return array{slug: string, theme_slug: string, religion_type: string}
     */
    public function toAttributes(): array
    {
        return [
            'slug' => $this->slug,
            'theme_slug' => $this->themeSlug,
            'religion_type' => $this->religionType,
        ];
    }
}
