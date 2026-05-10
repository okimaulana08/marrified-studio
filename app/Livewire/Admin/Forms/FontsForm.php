<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class FontsForm extends Form
{
    /** Font size scale presets — maps to --fs-scale CSS var consumed by render.css. */
    public const SCALE_PRESETS = [
        'compact' => 0.9,
        'normal' => 1.0,
        'spacious' => 1.1,
        'showcase' => 1.2,
    ];

    #[Validate('required|string|max:100')]
    public string $display = 'Playfair Display';

    #[Validate('required|string|max:100')]
    public string $body = 'Lato';

    #[Validate('required|string|max:100')]
    public string $script = 'Petit Formal Script';

    #[Validate('required|string|in:compact,normal,spacious,showcase')]
    public string $sizeScale = 'normal';

    public function fillFromManifest(array $fonts): void
    {
        $this->display = (string) ($fonts['display'] ?? 'Playfair Display');
        $this->body = (string) ($fonts['body'] ?? 'Lato');
        $this->script = (string) ($fonts['script'] ?? 'Petit Formal Script');
        $this->sizeScale = (string) ($fonts['size_scale'] ?? 'normal');

        if (! array_key_exists($this->sizeScale, self::SCALE_PRESETS)) {
            $this->sizeScale = 'normal';
        }
    }

    public function toManifestPatch(): array
    {
        return [
            'default_fonts' => [
                'display' => $this->display,
                'body' => $this->body,
                'script' => $this->script,
                'size_scale' => $this->sizeScale,
            ],
        ];
    }

    /** @return array<string, float> */
    public static function scalePresets(): array
    {
        return self::SCALE_PRESETS;
    }
}
