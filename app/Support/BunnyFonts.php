<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Builds Bunny Fonts CSS URL for the fonts a theme actually uses.
 *
 * The render and preview layouts pass the theme's font names (display/body/script)
 * here so each invitation only loads what it needs — no hardcoded font list,
 * and changing a theme's font in admin reflects on the next preview/render.
 */
final class BunnyFonts
{
    /** Default weights loaded for any family — covers headings, body, italics. */
    private const DEFAULT_WEIGHTS = '300,400,500,600,700';

    /**
     * @param  array<string, string>  $fonts  e.g. ['display' => 'Playfair Display', 'body' => 'Lato', 'script' => 'Great Vibes']
     */
    public static function url(array $fonts): string
    {
        $families = collect($fonts)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->map(fn ($name) => Str::slug($name).':'.self::DEFAULT_WEIGHTS)
            ->values()
            ->implode('|');

        if ($families === '') {
            return '';
        }

        return 'https://fonts.bunny.net/css?family='.$families.'&display=swap';
    }
}
