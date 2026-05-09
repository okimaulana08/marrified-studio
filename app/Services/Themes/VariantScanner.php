<?php

declare(strict_types=1);

namespace App\Services\Themes;

use Illuminate\Support\Facades\File;

final class VariantScanner
{
    private const SECTION_TYPES = [
        'cover', 'quotes', 'couple', 'story', 'event', 'countdown', 'gallery', 'gift', 'rsvp', 'guestbook', 'thanks',
    ];

    public function __construct(
        private readonly string $viewsPath,
    ) {}

    /**
     * @return array<string, list<string>>
     */
    public function all(): array
    {
        $result = [];

        foreach (self::SECTION_TYPES as $type) {
            $result[$type] = $this->forType($type);
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    public function forType(string $type): array
    {
        $dir = $this->viewsPath.DIRECTORY_SEPARATOR.$type;

        if (! File::isDirectory($dir)) {
            return [];
        }

        $variants = [];

        foreach (File::files($dir) as $file) {
            $filename = $file->getFilename();

            // Only process *.blade.php files
            if (! str_ends_with($filename, '.blade.php')) {
                continue;
            }

            // Skip partial includes (underscore prefix convention, e.g. _lightbox.blade.php)
            if (str_starts_with($filename, '_')) {
                continue;
            }

            // Strip double extension: "arch.blade.php" → getFilenameWithoutExtension() = "arch.blade" → strip ".blade"
            $name = $file->getFilenameWithoutExtension();
            $variant = str_ends_with($name, '.blade') ? substr($name, 0, -6) : $name;

            if ($variant !== '') {
                $variants[] = $variant;
            }
        }

        sort($variants);

        return $variants;
    }
}
