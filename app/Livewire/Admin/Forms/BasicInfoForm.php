<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class BasicInfoForm extends Form
{
    #[Validate('required|regex:/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/')]
    public string $slug = '';

    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    public bool $isPremium = false;

    public function fillFromManifest(array $data): void
    {
        $this->slug = (string) ($data['slug'] ?? '');
        $this->name = (string) ($data['name'] ?? '');
        $this->description = (string) ($data['description'] ?? '');
        $this->isPremium = (bool) ($data['is_premium'] ?? false);
    }

    public function toManifestPatch(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description !== '' ? $this->description : null,
            'is_premium' => $this->isPremium,
        ];
    }
}
