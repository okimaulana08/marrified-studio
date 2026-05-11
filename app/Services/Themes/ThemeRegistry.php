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
    public const SLOT_KEYS = [
        'corner-tl', 'corner-tr', 'corner-bl', 'corner-br',
        'edge-top', 'edge-bottom',
        'side-tc', 'side-bc', 'side-lc', 'side-rc',
        'middle',
    ];

    public const ANIM_PRESETS = [
        'fade-in', 'fade-down', 'fade-up', 'fade-left', 'fade-right',
        'slide-down', 'slide-up', 'scale-in', 'rotate-in', 'blur-in',
    ];

    /** Idle loop animations applied to slot inner img while slide is active. */
    public const ANIM_LOOP_PRESETS = [
        'sway', 'float', 'pulse', 'drift',
        'spin', 'spin-slow', 'bob', 'tilt', 'shimmer', 'wiggle', 'breathe', 'orbit',
    ];

    public const BG_FITS = ['cover', 'contain', 'fill', 'tile'];

    public const LOTTIE_PLACEMENTS = ['top', 'bottom', 'left', 'right', 'center'];

    public const LOTTIE_SIZES = ['small', 'medium', 'large', 'xlarge', 'full'];

    public const ALLOWED_EXTS = ['webp', 'svg', 'png', 'jpg', 'jpeg', 'json'];

    public const DURATION_MIN_MS = 100;

    public const DURATION_MAX_MS = 3000;

    public const DELAY_MAX_MS = 2000;

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

        $layout = (array) ($data['layout'] ?? []);
        if ($layout !== []) {
            $this->validateLayout($layout, $manifestPath);
        }

        return new Theme(
            slug: $slug,
            name: (string) ($data['name'] ?? $slug),
            description: isset($data['description']) ? (string) $data['description'] : null,
            isPremium: (bool) ($data['is_premium'] ?? false),
            defaultPalette: (array) ($data['default_palette'] ?? []),
            defaultFonts: (array) ($data['default_fonts'] ?? []),
            defaultSectionVariants: (array) ($data['default_section_variants'] ?? []),
            layout: $layout,
            customCss: isset($data['custom_css']) ? (string) $data['custom_css'] : '',
        );
    }

    /**
     * @param  array<string, mixed>  $layout
     */
    private function validateLayout(array $layout, string $manifestPath): void
    {
        if (isset($layout['default'])) {
            $this->validatePageLayout((array) $layout['default'], 'default', $manifestPath);
        }

        foreach ((array) ($layout['pages'] ?? []) as $page => $pageLayout) {
            $this->validatePageLayout((array) $pageLayout, "pages.{$page}", $manifestPath);
        }
    }

    /**
     * @param  array<string, mixed>  $page
     */
    private function validatePageLayout(array $page, string $context, string $manifestPath): void
    {
        if (isset($page['background'])) {
            $bg = (array) $page['background'];
            $file = (string) ($bg['file'] ?? '');
            if ($file === '') {
                throw new RuntimeException("Layout {$context}.background missing 'file' in {$manifestPath}");
            }
            $this->assertExtension($file, "{$context}.background.file", $manifestPath);

            $fit = (string) ($bg['fit'] ?? 'cover');
            if (! in_array($fit, self::BG_FITS, true)) {
                throw new RuntimeException("Layout {$context}.background.fit '{$fit}' invalid in {$manifestPath}");
            }
        }

        foreach ((array) ($page['slots'] ?? []) as $slotName => $slotEntry) {
            if (! in_array($slotName, self::SLOT_KEYS, true)) {
                throw new RuntimeException("Layout {$context}.slots.{$slotName}: unknown slot in {$manifestPath}");
            }
            if (! is_array($slotEntry)) {
                continue;
            }
            $file = (string) ($slotEntry['file'] ?? '');
            if ($file === '') {
                throw new RuntimeException("Layout {$context}.slots.{$slotName} missing 'file' in {$manifestPath}");
            }
            $this->assertExtension($file, "{$context}.slots.{$slotName}.file", $manifestPath);

            $anim = (string) ($slotEntry['anim_in'] ?? '');
            if ($anim !== '' && ! in_array($anim, self::ANIM_PRESETS, true)) {
                throw new RuntimeException("Layout {$context}.slots.{$slotName}.anim_in '{$anim}' invalid in {$manifestPath}");
            }

            if (isset($slotEntry['duration_ms'])) {
                $d = (int) $slotEntry['duration_ms'];
                if ($d < self::DURATION_MIN_MS || $d > self::DURATION_MAX_MS) {
                    throw new RuntimeException(
                        "Layout {$context}.slots.{$slotName}.duration_ms must be ".self::DURATION_MIN_MS.'-'.self::DURATION_MAX_MS."ms in {$manifestPath}"
                    );
                }
            }

            if (isset($slotEntry['delay_ms'])) {
                $d = (int) $slotEntry['delay_ms'];
                if ($d < 0 || $d > self::DELAY_MAX_MS) {
                    throw new RuntimeException(
                        "Layout {$context}.slots.{$slotName}.delay_ms must be 0-".self::DELAY_MAX_MS."ms in {$manifestPath}"
                    );
                }
            }
        }

        if (isset($page['lottie'])) {
            $l = (array) $page['lottie'];
            $file = (string) ($l['file'] ?? '');
            if ($file === '') {
                throw new RuntimeException("Layout {$context}.lottie missing 'file' in {$manifestPath}");
            }
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext !== 'json') {
                throw new RuntimeException("Layout {$context}.lottie.file must be .json (got '.{$ext}') in {$manifestPath}");
            }

            $placement = (string) ($l['placement'] ?? 'center');
            if (! in_array($placement, self::LOTTIE_PLACEMENTS, true)) {
                throw new RuntimeException("Layout {$context}.lottie.placement '{$placement}' invalid in {$manifestPath}");
            }

            if (isset($l['size'])) {
                $size = (string) $l['size'];
                if (! in_array($size, self::LOTTIE_SIZES, true)) {
                    throw new RuntimeException("Layout {$context}.lottie.size '{$size}' invalid in {$manifestPath}");
                }
            }
        }
    }

    private function assertExtension(string $file, string $context, string $manifestPath): void
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (! in_array($ext, self::ALLOWED_EXTS, true)) {
            throw new RuntimeException("Layout {$context} '{$file}' has disallowed extension '.{$ext}' in {$manifestPath}");
        }
    }
}
