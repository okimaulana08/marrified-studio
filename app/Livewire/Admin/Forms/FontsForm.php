<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class FontsForm extends Form
{
    #[Validate('required|string|max:100')]
    public string $display = 'Playfair Display';

    #[Validate('required|string|max:100')]
    public string $body = 'Lato';

    #[Validate('required|string|max:100')]
    public string $script = 'Petit Formal Script';

    public function fillFromManifest(array $fonts): void
    {
        $this->display = (string) ($fonts['display'] ?? 'Playfair Display');
        $this->body = (string) ($fonts['body'] ?? 'Lato');
        $this->script = (string) ($fonts['script'] ?? 'Petit Formal Script');
    }

    public function toManifestPatch(): array
    {
        return [
            'default_fonts' => [
                'display' => $this->display,
                'body' => $this->body,
                'script' => $this->script,
            ],
        ];
    }
}
