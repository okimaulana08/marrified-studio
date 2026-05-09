<?php

declare(strict_types=1);

namespace App\Services\Themes;

use Illuminate\Support\Facades\File;

final class AssetUsageAnalyzer
{
    public function __construct(
        private readonly string $themesPath,
    ) {}

    /**
     * Returns a list of human-readable usage locations for a given asset file.
     * Example: ["decorations.background", "sections.cover.frame", "sections.quotes.tossed"]
     *
     * @return list<string>
     */
    public function whereUsed(string $slug, string $filename): array
    {
        $manifestPath = $this->themesPath.DIRECTORY_SEPARATOR.$slug.DIRECTORY_SEPARATOR.'manifest.json';

        if (! File::exists($manifestPath)) {
            return [];
        }

        $data = (array) json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        $deco = (array) ($data['decorations'] ?? []);
        $usages = [];

        // Check background
        if (($deco['background']['file'] ?? '') === $filename) {
            $usages[] = 'decorations.background';
        }

        // Check per-section decorations
        $sections = (array) ($deco['sections'] ?? []);

        foreach ($sections as $type => $sectionDeco) {
            if (! is_array($sectionDeco)) {
                continue;
            }

            foreach (['frame', 'tossed', 'scene', 'icon'] as $key) {
                if (($sectionDeco[$key]['file'] ?? '') === $filename) {
                    $usages[] = "sections.{$type}.{$key}";
                }
            }
        }

        return $usages;
    }

    /**
     * Returns true if the asset is referenced anywhere in the manifest.
     */
    public function isUsed(string $slug, string $filename): bool
    {
        return $this->whereUsed($slug, $filename) !== [];
    }

    /**
     * Returns all filenames from the assets folder for a given theme.
     *
     * @return list<string>
     */
    public function listAssets(string $slug): array
    {
        $assetsDir = $this->themesPath.DIRECTORY_SEPARATOR.$slug.DIRECTORY_SEPARATOR.'assets';

        if (! File::isDirectory($assetsDir)) {
            return [];
        }

        return collect(File::files($assetsDir))
            ->map(fn ($f) => $f->getFilename())
            ->sort()
            ->values()
            ->all();
    }
}
