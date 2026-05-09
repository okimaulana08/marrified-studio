<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Forms;

use Livewire\Form;

final class VariantsForm extends Form
{
    public string $cover = 'arch';

    public string $quotes = 'default';

    public string $couple = 'side-by-side';

    public string $event = 'card';

    public string $gallery = 'grid';

    public string $gift = 'cashless-modal';

    public string $rsvp = 'default';

    public string $guestbook = 'default';

    /**
     * Livewire requires a rules definition before `validate()` can be called.
     * Variant existence (each value points to a real Blade file) is checked
     * cross-field by ThemeValidator::validateVariantsExist after toManifestPatch.
     */
    public function rules(): array
    {
        return [];
    }

    public function fillFromManifest(array $variants): void
    {
        $this->cover = (string) ($variants['cover'] ?? 'arch');
        $this->quotes = (string) ($variants['quotes'] ?? 'default');
        $this->couple = (string) ($variants['couple'] ?? 'side-by-side');
        $this->event = (string) ($variants['event'] ?? 'card');
        $this->gallery = (string) ($variants['gallery'] ?? 'grid');
        $this->gift = (string) ($variants['gift'] ?? 'cashless-modal');
        $this->rsvp = (string) ($variants['rsvp'] ?? 'default');
        $this->guestbook = (string) ($variants['guestbook'] ?? 'default');
    }

    public function toManifestPatch(): array
    {
        return [
            'default_section_variants' => [
                'cover' => $this->cover,
                'quotes' => $this->quotes,
                'couple' => $this->couple,
                'event' => $this->event,
                'gallery' => $this->gallery,
                'gift' => $this->gift,
                'rsvp' => $this->rsvp,
                'guestbook' => $this->guestbook,
            ],
        ];
    }
}
