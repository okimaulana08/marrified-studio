<?php

declare(strict_types=1);

namespace App\Services\Themes;

use Illuminate\Support\Facades\File;
use RuntimeException;

final class ThemeCloner
{
    private const SLUG_REGEX = '/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/';

    public function __construct(
        private readonly ThemeRegistry $registry,
        private readonly string $themesPath,
    ) {}

    public function clone(string $sourceSlug, string $targetSlug): void
    {
        if (! preg_match(self::SLUG_REGEX, $targetSlug)) {
            throw new RuntimeException("Invalid target slug format: '{$targetSlug}'.");
        }

        $sourceDir = $this->themesPath.DIRECTORY_SEPARATOR.$sourceSlug;
        $targetDir = $this->themesPath.DIRECTORY_SEPARATOR.$targetSlug;

        if (! File::isDirectory($sourceDir)) {
            throw new RuntimeException("Source theme '{$sourceSlug}' not found.");
        }

        if (File::isDirectory($targetDir)) {
            throw new RuntimeException("Target theme '{$targetSlug}' already exists.");
        }

        // Copy entire theme directory recursively
        File::copyDirectory($sourceDir, $targetDir);

        // Update slug + name in the cloned manifest
        $manifestPath = $targetDir.DIRECTORY_SEPARATOR.'manifest.json';
        $data = (array) json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        $data['slug'] = $targetSlug;
        $data['name'] = $data['name'].' (Copy)';

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        File::put($manifestPath, $json);

        // Publish assets inline (faster + no container/registry-cache gymnastics
        // compared to Artisan::call('themes:publish-assets')). Mirror the same
        // logic as the CLI command — skip .blade.php files, mirror sub-tree.
        $this->publishAssets($targetDir, $targetSlug);

        $this->registry->flush();
    }

    private function publishAssets(string $themeDir, string $slug): void
    {
        $source = $themeDir.DIRECTORY_SEPARATOR.'assets';
        if (! File::isDirectory($source)) {
            return;
        }

        $target = public_path("themes/{$slug}");
        File::ensureDirectoryExists($target);

        foreach (File::allFiles($source) as $file) {
            $rel = $file->getRelativePathname();
            if (str_ends_with($rel, '.blade.php')) {
                continue;
            }
            $dest = $target.DIRECTORY_SEPARATOR.$rel;
            File::ensureDirectoryExists(dirname($dest));
            File::copy($file->getPathname(), $dest);
        }
    }
}
