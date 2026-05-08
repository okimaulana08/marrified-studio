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
}
