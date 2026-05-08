<?php

declare(strict_types=1);

namespace App\Services\Themes;

use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Loads themes from `resources/themes/{slug}/manifest.json`.
 * Filesystem-backed; no DB row needed in the PoC.
 */
final class ThemeRegistry
{
    private const ALLOWED_SLOTS = ['top', 'bottom', 'tl', 'tr', 'bl', 'br', 'center', 'cover-overlay'];

    private const ALLOWED_EXTS = ['webp', 'svg', 'png', 'jpg', 'jpeg', 'json'];

    /** @var array<string, Theme>|null */
    private ?array $cache = null;

    public function __construct(
        private readonly string $themesPath,
    ) {}

    /**
     * @return array<string, Theme>
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $themes = [];

        if (! File::isDirectory($this->themesPath)) {
            return $this->cache = [];
        }

        foreach (File::directories($this->themesPath) as $dir) {
            $manifestPath = $dir.DIRECTORY_SEPARATOR.'manifest.json';
            if (! File::exists($manifestPath)) {
                continue;
            }

            $theme = $this->parse($manifestPath);
            $themes[$theme->slug] = $theme;
        }

        return $this->cache = $themes;
    }

    public function find(string $slug): ?Theme
    {
        return $this->all()[$slug] ?? null;
    }

    public function path(string $slug): string
    {
        return $this->themesPath.DIRECTORY_SEPARATOR.$slug;
    }

    public function flush(): void
    {
        $this->cache = null;
    }

    private function parse(string $manifestPath): Theme
    {
        $raw = File::get($manifestPath);
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new RuntimeException("Invalid manifest JSON at {$manifestPath}");
        }

        $slug = (string) ($data['slug'] ?? '');
        if ($slug === '') {
            throw new RuntimeException("Manifest missing 'slug' at {$manifestPath}");
        }

        if (isset($data['decorations'])) {
            $this->validateDecorations((array) $data['decorations'], $manifestPath);
        }

        return new Theme(
            slug: $slug,
            name: (string) ($data['name'] ?? $slug),
            description: isset($data['description']) ? (string) $data['description'] : null,
            isPremium: (bool) ($data['is_premium'] ?? false),
            defaultPalette: (array) ($data['default_palette'] ?? []),
            defaultFonts: (array) ($data['default_fonts'] ?? []),
            defaultSectionVariants: (array) ($data['default_section_variants'] ?? []),
            decorations: (array) ($data['decorations'] ?? []),
        );
    }

    /**
     * @param  array<string, mixed>  $deco
     */
    private function validateDecorations(array $deco, string $manifestPath): void
    {
        $sections = (array) ($deco['sections'] ?? []);
        foreach ($sections as $key => $sectionDeco) {
            if (! is_array($sectionDeco)) {
                continue;
            }
            foreach (['frame', 'tossed', 'scene', 'icon'] as $slot) {
                $entry = $sectionDeco[$slot] ?? null;
                if (! is_array($entry)) {
                    continue;
                }
                $file = (string) ($entry['file'] ?? '');
                if ($file === '') {
                    throw new RuntimeException("Decoration {$key}.{$slot} missing 'file' in {$manifestPath}");
                }
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (! in_array($ext, self::ALLOWED_EXTS, true)) {
                    throw new RuntimeException("Decoration {$key}.{$slot}.file '{$file}' has disallowed extension '.{$ext}' in {$manifestPath}");
                }
                $entrySlot = (string) ($entry['slot'] ?? '');
                if ($entrySlot !== '' && ! in_array($entrySlot, self::ALLOWED_SLOTS, true)) {
                    throw new RuntimeException("Decoration {$key}.{$slot}.slot '{$entrySlot}' is invalid in {$manifestPath}");
                }
            }
        }
    }
}
