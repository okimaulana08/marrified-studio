<?php

declare(strict_types=1);

namespace App\Services\Themes;

use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;

final class ThemeValidator
{
    /** Required top-level keys in a Lottie/Bodymovin JSON. */
    private const LOTTIE_REQUIRED_KEYS = ['v', 'fr', 'ip', 'op', 'w', 'h', 'layers'];

    public function __construct(
        private readonly VariantScanner $variants,
        private readonly string $themesPath,
    ) {}

    /**
     * Cross-field validation that runs after per-form field validation.
     * Checks asset references exist on disk + blade variant files exist + slot/anim/fit values are valid.
     *
     * @throws RuntimeException
     */
    public function validate(string $slug, array $manifestPatch): void
    {
        $this->validateAssetReferences($slug, $manifestPatch);
        $this->validateVariantsExist($manifestPatch);
        $this->validateLayoutEnums($manifestPatch);
    }

    private function validateAssetReferences(string $slug, array $patch): void
    {
        $layout = (array) ($patch['layout'] ?? []);
        if ($layout === []) {
            return;
        }

        if (isset($layout['default'])) {
            $this->checkPageAssets($slug, (array) $layout['default'], 'layout.default');
        }

        foreach ((array) ($layout['pages'] ?? []) as $page => $pageLayout) {
            if (is_array($pageLayout)) {
                $this->checkPageAssets($slug, $pageLayout, "layout.pages.{$page}");
            }
        }
    }

    private function checkPageAssets(string $slug, array $page, string $context): void
    {
        if (isset($page['background']['file']) && $page['background']['file'] !== '') {
            $this->assertAssetExists($slug, (string) $page['background']['file'], "{$context}.background.file");
        }

        foreach ((array) ($page['slots'] ?? []) as $slotName => $slotEntry) {
            if (! is_array($slotEntry)) {
                continue;
            }
            $file = (string) ($slotEntry['file'] ?? '');
            if ($file !== '') {
                $this->assertAssetExists($slug, $file, "{$context}.slots.{$slotName}.file");
            }
        }

        if (isset($page['lottie']['file']) && $page['lottie']['file'] !== '') {
            $file = (string) $page['lottie']['file'];
            $this->assertAssetExists($slug, $file, "{$context}.lottie.file");
            $this->assertLottieJsonStructure($slug, $file, "{$context}.lottie.file");
        }
    }

    private function validateVariantsExist(array $patch): void
    {
        $sectionVariants = (array) ($patch['default_section_variants'] ?? []);

        foreach ($sectionVariants as $type => $variant) {
            $available = $this->variants->forType((string) $type);

            if ($available !== [] && ! in_array($variant, $available, true)) {
                throw new RuntimeException(
                    "Section variant '{$variant}' for type '{$type}' does not exist. Available: ".implode(', ', $available)
                );
            }
        }
    }

    private function validateLayoutEnums(array $patch): void
    {
        $layout = (array) ($patch['layout'] ?? []);
        if ($layout === []) {
            return;
        }

        if (isset($layout['default'])) {
            $this->checkPageEnums((array) $layout['default'], 'layout.default');
        }

        foreach ((array) ($layout['pages'] ?? []) as $page => $pageLayout) {
            if (is_array($pageLayout)) {
                $this->checkPageEnums($pageLayout, "layout.pages.{$page}");
            }
        }
    }

    private function checkPageEnums(array $page, string $context): void
    {
        if (isset($page['background']['fit'])) {
            $fit = (string) $page['background']['fit'];
            if ($fit !== '' && ! in_array($fit, ThemeRegistry::BG_FITS, true)) {
                throw new RuntimeException(
                    "Invalid background.fit '{$fit}' at {$context}. Allowed: ".implode(', ', ThemeRegistry::BG_FITS)
                );
            }
        }

        foreach ((array) ($page['slots'] ?? []) as $slotName => $slotEntry) {
            if (! in_array($slotName, ThemeRegistry::SLOT_KEYS, true)) {
                throw new RuntimeException(
                    "Unknown slot '{$slotName}' at {$context}.slots. Allowed: ".implode(', ', ThemeRegistry::SLOT_KEYS)
                );
            }
            if (! is_array($slotEntry)) {
                continue;
            }

            if (($slotEntry['anim_in'] ?? '') !== '') {
                $anim = (string) $slotEntry['anim_in'];
                if (! in_array($anim, ThemeRegistry::ANIM_PRESETS, true)) {
                    throw new RuntimeException(
                        "Invalid anim_in '{$anim}' at {$context}.slots.{$slotName}. Allowed: ".implode(', ', ThemeRegistry::ANIM_PRESETS)
                    );
                }
            }

            if (isset($slotEntry['duration_ms'])) {
                $d = (int) $slotEntry['duration_ms'];
                if ($d < ThemeRegistry::DURATION_MIN_MS || $d > ThemeRegistry::DURATION_MAX_MS) {
                    throw new RuntimeException(
                        "Invalid duration_ms {$d} at {$context}.slots.{$slotName}. Must be "
                        .ThemeRegistry::DURATION_MIN_MS.'-'.ThemeRegistry::DURATION_MAX_MS.'ms.'
                    );
                }
            }

            if (isset($slotEntry['delay_ms'])) {
                $d = (int) $slotEntry['delay_ms'];
                if ($d < 0 || $d > ThemeRegistry::DELAY_MAX_MS) {
                    throw new RuntimeException(
                        "Invalid delay_ms {$d} at {$context}.slots.{$slotName}. Must be 0-".ThemeRegistry::DELAY_MAX_MS.'ms.'
                    );
                }
            }
        }

        if (isset($page['lottie']['placement'])) {
            $placement = (string) $page['lottie']['placement'];
            if ($placement !== '' && ! in_array($placement, ThemeRegistry::LOTTIE_PLACEMENTS, true)) {
                throw new RuntimeException(
                    "Invalid lottie.placement '{$placement}' at {$context}. Allowed: ".implode(', ', ThemeRegistry::LOTTIE_PLACEMENTS)
                );
            }
        }

        if (isset($page['lottie']['size'])) {
            $size = (string) $page['lottie']['size'];
            if ($size !== '' && ! in_array($size, ThemeRegistry::LOTTIE_SIZES, true)) {
                throw new RuntimeException(
                    "Invalid lottie.size '{$size}' at {$context}. Allowed: ".implode(', ', ThemeRegistry::LOTTIE_SIZES)
                );
            }
        }
    }

    private function assertAssetExists(string $slug, string $filename, string $fieldPath): void
    {
        $assetPath = $this->themesPath.DIRECTORY_SEPARATOR.$slug.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$filename;

        if (! File::exists($assetPath)) {
            throw new RuntimeException(
                "Asset '{$filename}' referenced in '{$fieldPath}' does not exist in theme '{$slug}' assets folder."
            );
        }
    }

    /**
     * Structural validation for a Lottie/Bodymovin JSON file.
     * Catches obvious malformations at save-time so they don't surface as
     * silent runtime failures inside the lottie-web player on the public render.
     */
    private function assertLottieJsonStructure(string $slug, string $filename, string $fieldPath): void
    {
        if (! str_ends_with(strtolower($filename), '.json')) {
            throw new RuntimeException(
                "Lottie '{$filename}' at '{$fieldPath}' must have a .json extension."
            );
        }

        $assetPath = $this->themesPath.DIRECTORY_SEPARATOR.$slug.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$filename;
        $contents = File::get($assetPath);

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException(
                "Lottie file '{$filename}' at '{$fieldPath}' is not valid JSON: ".$e->getMessage()
            );
        }

        if (! is_array($data)) {
            throw new RuntimeException(
                "Lottie file '{$filename}' at '{$fieldPath}' must be a JSON object, got ".gettype($data).'.'
            );
        }

        $missing = [];
        foreach (self::LOTTIE_REQUIRED_KEYS as $key) {
            if (! array_key_exists($key, $data)) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException(
                "Lottie file '{$filename}' at '{$fieldPath}' missing required key(s) '"
                .implode("', '", $missing)
                ."'. This does not look like a valid Bodymovin/Lottie export."
            );
        }

        if (! is_array($data['layers']) || $data['layers'] === []) {
            throw new RuntimeException(
                "Lottie file '{$filename}' at '{$fieldPath}' has empty 'layers' array — animation has no content."
            );
        }
    }
}
