<?php

declare(strict_types=1);

namespace App\Support;

final class ThemeAsset
{
    public static function url(string $slug, string $file): string
    {
        return asset("themes/{$slug}/{$file}");
    }

    public static function publicPath(string $slug, string $file): string
    {
        return public_path("themes/{$slug}/{$file}");
    }

    /**
     * Find the first existing preview file across supported extensions.
     * Returns the filename (e.g. "preview.webp") or null if none exists.
     * Used by the theme list to render whatever preview format the admin uploaded.
     */
    public static function findPreview(string $slug): ?string
    {
        foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
            $file = "preview.{$ext}";
            if (file_exists(self::publicPath($slug, $file))) {
                return $file;
            }
        }

        return null;
    }
}
