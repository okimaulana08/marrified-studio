<?php

declare(strict_types=1);

namespace App\Services\Themes;

/**
 * Plain value object representing a theme loaded from manifest.json.
 * Read-only; all data comes from the filesystem manifest, not DB.
 */
final readonly class Theme
{
    /**
     * @param  array<string, string>  $defaultPalette
     * @param  array<string, string>  $defaultFonts
     * @param  array<string, string>  $defaultSectionVariants
     * @param  array{default?: array<string, mixed>, pages?: array<string, array<string, mixed>>}  $layout
     */
    public function __construct(
        public string $slug,
        public string $name,
        public ?string $description,
        public bool $isPremium,
        public array $defaultPalette,
        public array $defaultFonts,
        public array $defaultSectionVariants,
        public array $layout,
    ) {}

    public function variantFor(string $sectionType, ?string $explicit = null): string
    {
        if ($explicit !== null && $explicit !== '') {
            return $explicit;
        }

        return $this->defaultSectionVariants[$sectionType] ?? 'default';
    }

    /**
     * Returns merged background config for a page (default + per-page override).
     *
     * @return array{file: string, opacity?: float, fit?: string}|null
     */
    public function background(?string $page = null): ?array
    {
        $bg = $this->layoutFor($page)['background'] ?? null;

        if (! is_array($bg) || ($bg['file'] ?? '') === '') {
            return null;
        }

        return $bg;
    }

    /**
     * Returns merged slots map (slotName => config) for a page.
     * Empty entries (no file) are filtered out.
     *
     * @return array<string, array{file: string, anim_in?: string}>
     */
    public function slots(?string $page = null): array
    {
        $slots = $this->layoutFor($page)['slots'] ?? [];

        if (! is_array($slots)) {
            return [];
        }

        return array_filter(
            $slots,
            fn ($entry) => is_array($entry) && ($entry['file'] ?? '') !== ''
        );
    }

    /**
     * Returns merged lottie config for a page, or null if none.
     *
     * @return array{file: string, placement?: string, loop?: bool}|null
     */
    public function lottie(?string $page = null): ?array
    {
        $l = $this->layoutFor($page)['lottie'] ?? null;

        if (! is_array($l) || ($l['file'] ?? '') === '') {
            return null;
        }

        return $l;
    }

    /**
     * @return array{background?: array<string, mixed>, slots?: array<string, mixed>, lottie?: array<string, mixed>}
     */
    private function layoutFor(?string $page): array
    {
        $default = (array) ($this->layout['default'] ?? []);

        if ($page === null || ! isset($this->layout['pages'][$page])) {
            return $default;
        }

        $override = (array) $this->layout['pages'][$page];

        $merged = $default;

        // bg + lottie: override fully replaces default if present.
        // slots: shallow merge per slot key.
        foreach (['background', 'lottie'] as $key) {
            if (isset($override[$key])) {
                $merged[$key] = $override[$key];
            }
        }

        if (isset($override['slots'])) {
            $merged['slots'] = array_merge(
                (array) ($default['slots'] ?? []),
                (array) $override['slots']
            );
        }

        return $merged;
    }
}
