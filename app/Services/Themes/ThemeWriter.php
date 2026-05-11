<?php

declare(strict_types=1);

namespace App\Services\Themes;

use Illuminate\Support\Facades\File;
use RuntimeException;

final class ThemeWriter
{
    private const SLUG_REGEX = '/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/';

    private const DEFAULT_PALETTE = [
        'primary' => '#5d8068',
        'accent' => '#c9a96e',
        'accent2' => '#3d5a47',
        'bg' => '#eef2ea',
        'ink' => '#2a3d31',
        'muted' => '#6f7e72',
    ];

    private const DEFAULT_FONTS = [
        'display' => 'Playfair Display',
        'body' => 'Lato',
        'script' => 'Petit Formal Script',
    ];

    private const DEFAULT_VARIANTS = [
        'cover' => 'arch',
        'quotes' => 'default',
        'couple' => 'side-by-side',
        'event' => 'card',
        'gallery' => 'grid',
        'gift' => 'cashless-modal',
        'rsvp' => 'default',
        'guestbook' => 'default',
    ];

    public function __construct(
        private readonly ThemeRegistry $registry,
        private readonly string $themesPath,
    ) {}

    public function createTheme(string $slug, string $name, bool $isPremium = false): void
    {
        if (! preg_match(self::SLUG_REGEX, $slug)) {
            throw new RuntimeException("Invalid slug format: '{$slug}'. Must be lowercase alphanumeric with hyphens, 3-50 chars.");
        }

        $themeDir = $this->themesPath.DIRECTORY_SEPARATOR.$slug;

        if (File::isDirectory($themeDir)) {
            throw new RuntimeException("Theme '{$slug}' already exists.");
        }

        File::makeDirectory($themeDir.DIRECTORY_SEPARATOR.'assets', 0755, recursive: true);

        $skeleton = [
            'slug' => $slug,
            'name' => $name,
            'description' => null,
            'is_premium' => $isPremium,
            'default_palette' => self::DEFAULT_PALETTE,
            'default_fonts' => self::DEFAULT_FONTS,
            'default_section_variants' => self::DEFAULT_VARIANTS,
            'layout' => [
                'default' => [
                    'slots' => (object) [],
                ],
                'pages' => (object) [],
            ],
        ];

        $this->atomicWrite($themeDir.DIRECTORY_SEPARATOR.'manifest.json', $skeleton);
        $this->registry->flush();
    }

    /**
     * Permanently delete a theme: source dir + published asset dir + registry
     * cache flush. Caller is responsible for verifying no invitations still
     * reference this slug (FK to text column, no DB cascade).
     */
    public function deleteTheme(string $slug): void
    {
        if (! preg_match(self::SLUG_REGEX, $slug)) {
            throw new RuntimeException("Invalid slug format: '{$slug}'.");
        }

        $themeDir = $this->themesPath.DIRECTORY_SEPARATOR.$slug;
        $publicDir = public_path("themes/{$slug}");

        if (! File::isDirectory($themeDir)) {
            throw new RuntimeException("Theme '{$slug}' tidak ditemukan.");
        }

        File::deleteDirectory($themeDir);

        if (File::isDirectory($publicDir)) {
            File::deleteDirectory($publicDir);
        }

        $this->registry->flush();
    }

    /**
     * Merge $patch into the existing manifest and write atomically.
     * Strips empty layout entries (file === '') before writing.
     */
    public function writeManifest(string $slug, array $patch): void
    {
        $themeDir = $this->themesPath.DIRECTORY_SEPARATOR.$slug;

        if (! File::isDirectory($themeDir)) {
            throw new RuntimeException("Theme directory not found for slug '{$slug}'.");
        }

        $manifestPath = $themeDir.DIRECTORY_SEPARATOR.'manifest.json';
        $current = File::exists($manifestPath)
            ? (array) json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR)
            : [];

        $merged = array_merge($current, $patch);
        $merged['slug'] = $slug;

        if (isset($merged['layout'])) {
            $merged['layout'] = $this->stripEmptyLayout((array) $merged['layout']);
        }

        $this->atomicWrite($manifestPath, $merged);
        $this->registry->flush();
    }

    /**
     * Returns raw manifest array for editing (not a Theme value object).
     */
    public function readManifestArray(string $slug): array
    {
        $path = $this->themesPath.DIRECTORY_SEPARATOR.$slug.DIRECTORY_SEPARATOR.'manifest.json';

        if (! File::exists($path)) {
            return [];
        }

        return (array) json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function atomicWrite(string $finalPath, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $tmpPath = $finalPath.'.tmp.'.bin2hex(random_bytes(4));

        File::put($tmpPath, $json);

        if (! @rename($tmpPath, $finalPath)) {
            @unlink($tmpPath);
            throw new RuntimeException("Failed to write manifest to {$finalPath}");
        }
    }

    /**
     * Strip empty entries (file === '') from background, slots, lottie at every page level.
     *
     * @param  array<string, mixed>  $layout
     * @return array<string, mixed>
     */
    private function stripEmptyLayout(array $layout): array
    {
        if (isset($layout['default'])) {
            $layout['default'] = $this->stripEmptyPage((array) $layout['default']);
        }

        if (isset($layout['pages']) && is_array($layout['pages'])) {
            foreach ($layout['pages'] as $page => $pageLayout) {
                if (! is_array($pageLayout)) {
                    continue;
                }
                $cleaned = $this->stripEmptyPage($pageLayout);
                if ($cleaned === []) {
                    unset($layout['pages'][$page]);
                } else {
                    $layout['pages'][$page] = $cleaned;
                }
            }
        }

        return $layout;
    }

    /**
     * @param  array<string, mixed>  $page
     * @return array<string, mixed>
     */
    private function stripEmptyPage(array $page): array
    {
        if (isset($page['background']) && ($page['background']['file'] ?? '') === '') {
            unset($page['background']);
        }

        if (isset($page['lottie']) && ($page['lottie']['file'] ?? '') === '') {
            unset($page['lottie']);
        }

        if (isset($page['slots']) && is_array($page['slots'])) {
            foreach ($page['slots'] as $name => $entry) {
                if (! is_array($entry) || ($entry['file'] ?? '') === '') {
                    unset($page['slots'][$name]);
                }
            }
            if ($page['slots'] === []) {
                unset($page['slots']);
            }
        }

        return $page;
    }
}
