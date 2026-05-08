<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Themes\ThemeRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ThemesPublishAssets extends Command
{
    protected $signature = 'themes:publish-assets {slug? : Specific theme slug to publish, or all if omitted} {--clean : Wipe target dir before publish}';

    protected $description = 'Copy theme assets from resources/themes/{slug}/assets/ to public/themes/{slug}/';

    public function handle(ThemeRegistry $registry): int
    {
        $slug = $this->argument('slug');
        $themes = $slug
            ? array_filter([$registry->find((string) $slug)])
            : array_values($registry->all());

        if (empty($themes)) {
            $this->warn($slug ? "Theme '{$slug}' not found." : 'No themes registered.');

            return self::SUCCESS;
        }

        $totalCopied = 0;

        foreach ($themes as $theme) {
            $source = $registry->path($theme->slug).DIRECTORY_SEPARATOR.'assets';
            $target = public_path("themes/{$theme->slug}");

            if (! File::isDirectory($source)) {
                $this->components->twoColumnDetail($theme->slug, '<fg=gray>skip (no assets/)</>');

                continue;
            }

            if ($this->option('clean') && File::isDirectory($target)) {
                File::deleteDirectory($target);
            }

            File::ensureDirectoryExists($target);

            $copied = 0;
            foreach (File::allFiles($source) as $file) {
                $rel = $file->getRelativePathname();
                if (str_ends_with($rel, '.blade.php')) {
                    continue;
                }
                $dest = $target.DIRECTORY_SEPARATOR.$rel;
                File::ensureDirectoryExists(dirname($dest));
                File::copy($file->getPathname(), $dest);
                $copied++;
            }

            $totalCopied += $copied;
            $this->components->twoColumnDetail($theme->slug, "<fg=green>{$copied} files</>");
        }

        $this->info("Published {$totalCopied} asset(s).");

        return self::SUCCESS;
    }
}
