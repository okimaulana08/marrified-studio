<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class PaletteForm extends Form
{
    #[Validate('required|regex:/^#[0-9a-fA-F]{3,6}$/')]
    public string $primary = '#5d8068';

    #[Validate('required|regex:/^#[0-9a-fA-F]{3,6}$/')]
    public string $accent = '#c9a96e';

    #[Validate('required|regex:/^#[0-9a-fA-F]{3,6}$/')]
    public string $accent2 = '#3d5a47';

    #[Validate('required|regex:/^#[0-9a-fA-F]{3,6}$/')]
    public string $bg = '#eef2ea';

    #[Validate('required|regex:/^#[0-9a-fA-F]{3,6}$/')]
    public string $ink = '#2a3d31';

    #[Validate('required|regex:/^#[0-9a-fA-F]{3,6}$/')]
    public string $muted = '#6f7e72';

    /** @var array<string, array<string, string>> */
    public const PRESETS = [
        'earthy-garden' => [
            'name' => 'Earthy Garden',
            'primary' => '#5d8068',
            'accent' => '#c9a96e',
            'accent2' => '#3d5a47',
            'bg' => '#eef2ea',
            'ink' => '#2a3d31',
            'muted' => '#6f7e72',
        ],
        'royal-navy' => [
            'name' => 'Royal Navy',
            'primary' => '#1e3a5f',
            'accent' => '#c9a028',
            'accent2' => '#2c5282',
            'bg' => '#f0f4f8',
            'ink' => '#1a202c',
            'muted' => '#718096',
        ],
        'soft-pastel' => [
            'name' => 'Soft Pastel',
            'primary' => '#c084a0',
            'accent' => '#f0c4a4',
            'accent2' => '#a06080',
            'bg' => '#fdf6f8',
            'ink' => '#4a2838',
            'muted' => '#a08090',
        ],
        'monochrome' => [
            'name' => 'Monochrome',
            'primary' => '#374151',
            'accent' => '#6b7280',
            'accent2' => '#111827',
            'bg' => '#f9fafb',
            'ink' => '#111827',
            'muted' => '#9ca3af',
        ],
        'blush-gold' => [
            'name' => 'Blush Gold',
            'primary' => '#b76e79',
            'accent' => '#d4a853',
            'accent2' => '#8c4a56',
            'bg' => '#fdf8f4',
            'ink' => '#3d1f27',
            'muted' => '#a08060',
        ],
    ];

    public function fillFromManifest(array $palette): void
    {
        $this->primary = (string) ($palette['primary'] ?? '#5d8068');
        $this->accent = (string) ($palette['accent'] ?? '#c9a96e');
        $this->accent2 = (string) ($palette['accent2'] ?? '#3d5a47');
        $this->bg = (string) ($palette['bg'] ?? '#eef2ea');
        $this->ink = (string) ($palette['ink'] ?? '#2a3d31');
        $this->muted = (string) ($palette['muted'] ?? '#6f7e72');
    }

    public function applyPreset(string $key): void
    {
        $preset = self::PRESETS[$key] ?? null;

        if ($preset === null) {
            return;
        }

        $this->primary = $preset['primary'];
        $this->accent = $preset['accent'];
        $this->accent2 = $preset['accent2'];
        $this->bg = $preset['bg'];
        $this->ink = $preset['ink'];
        $this->muted = $preset['muted'];
    }

    public function toManifestPatch(): array
    {
        return [
            'default_palette' => [
                'primary' => $this->primary,
                'accent' => $this->accent,
                'accent2' => $this->accent2,
                'bg' => $this->bg,
                'ink' => $this->ink,
                'muted' => $this->muted,
            ],
        ];
    }
}
