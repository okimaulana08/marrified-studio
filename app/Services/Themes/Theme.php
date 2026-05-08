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
     * @param  array<string, mixed>  $decorations
     */
    public function __construct(
        public string $slug,
        public string $name,
        public ?string $description,
        public bool $isPremium,
        public array $defaultPalette,
        public array $defaultFonts,
        public array $defaultSectionVariants,
        public array $decorations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function decorationFor(string $sectionType): array
    {
        return $this->decorations['sections'][$sectionType] ?? [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function background(): ?array
    {
        return $this->decorations['background'] ?? null;
    }

    public function variantFor(string $sectionType, ?string $explicit = null): string
    {
        if ($explicit !== null && $explicit !== '') {
            return $explicit;
        }

        return $this->defaultSectionVariants[$sectionType] ?? 'default';
    }
}
