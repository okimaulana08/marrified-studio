<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Forms;

use App\Support\CustomCss;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class CustomCssForm extends Form
{
    #[Validate('nullable|string|max:30000')]
    public string $customCss = '';

    public function fillFromManifest(array $manifest): void
    {
        $this->customCss = (string) ($manifest['custom_css'] ?? '');
    }

    public function toManifestPatch(): array
    {
        $clean = CustomCss::sanitize($this->customCss);

        return [
            'custom_css' => $clean !== '' ? $clean : null,
        ];
    }
}
