<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Forms;

use App\Services\Themes\ThemeRegistry;
use Livewire\Form;

final class LayoutForm extends Form
{
    /** Section types eligible for per-page slot overrides (matches sections we render). */
    public const SECTION_TYPES = ['cover', 'quotes', 'couple', 'story', 'event', 'gallery', 'gift', 'rsvp', 'guestbook', 'thanks'];

    /* ---------- Default page: Background ---------- */
    public string $bgFile = '';

    public float $bgOpacity = 0.5;

    public string $bgFit = 'cover';

    /* ---------- Default page: 11 named slots ---------- */
    /** @var array<string, array{file: string, anim_in: string, duration_ms: int, delay_ms: int}> */
    public array $slots = [];

    /* ---------- Default page: Lottie ---------- */
    public string $lottieFile = '';

    public string $lottiePlacement = 'center';

    public bool $lottieLoop = true;

    public string $lottieSize = 'medium';

    /* ---------- Per-page slot overrides ---------- */
    /**
     * Pre-initialized for every section type so wire:model paths bind without
     * null reference errors. Each entry has 11 slot keys, each with the same
     * structure as the default slots property.
     *
     * @var array<string, array{slots: array<string, array{file: string, anim_in: string, duration_ms: int, delay_ms: int}>}>
     */
    public array $pages = [];

    /* ---------- UI state ---------- */
    /** Active page being edited; '' = editing default. */
    public string $activePage = '';

    public function __construct()
    {
        $this->initSlots();
        $this->initPages();
    }

    /**
     * Livewire requires a rules definition before `validate()` can be called.
     * All cross-field validation (asset existence, slot keys, anim presets,
     * duration/delay ranges, lottie structure) lives in ThemeValidator and
     * runs after toManifestPatch(), so this form has no field-level rules.
     */
    public function rules(): array
    {
        return [];
    }

    public function initSlots(): void
    {
        $this->slots = $this->emptySlotMap();
    }

    public function initPages(): void
    {
        foreach (self::SECTION_TYPES as $type) {
            $this->pages[$type] = ['slots' => $this->emptySlotMap()];
        }
    }

    /** @return array<string, array{file: string, anim_in: string, duration_ms: int, delay_ms: int}> */
    private function emptySlotMap(): array
    {
        $map = [];
        foreach (ThemeRegistry::SLOT_KEYS as $key) {
            $map[$key] = ['file' => '', 'anim_in' => '', 'duration_ms' => 0, 'delay_ms' => 0];
        }

        return $map;
    }

    public function fillFromManifest(array $layout): void
    {
        $this->initSlots();
        $this->initPages();

        // Default page
        $default = (array) ($layout['default'] ?? []);

        $bg = $default['background'] ?? null;
        if (is_array($bg)) {
            $this->bgFile = (string) ($bg['file'] ?? '');
            $this->bgOpacity = (float) ($bg['opacity'] ?? 0.5);
            $this->bgFit = (string) ($bg['fit'] ?? 'cover');
        }

        $this->fillSlotsInto($this->slots, (array) ($default['slots'] ?? []));

        $lottie = $default['lottie'] ?? null;
        if (is_array($lottie)) {
            $this->lottieFile = (string) ($lottie['file'] ?? '');
            $this->lottiePlacement = (string) ($lottie['placement'] ?? 'center');
            $this->lottieLoop = (bool) ($lottie['loop'] ?? true);
            $this->lottieSize = (string) ($lottie['size'] ?? 'medium');
        }

        // Per-page overrides
        foreach ((array) ($layout['pages'] ?? []) as $type => $pageLayout) {
            if (! isset($this->pages[$type]) || ! is_array($pageLayout)) {
                continue;
            }
            $this->fillSlotsInto($this->pages[$type]['slots'], (array) ($pageLayout['slots'] ?? []));
        }
    }

    /**
     * @param  array<string, array{file: string, anim_in: string, duration_ms: int, delay_ms: int}>  $target
     * @param  array<string, mixed>  $source
     */
    private function fillSlotsInto(array &$target, array $source): void
    {
        foreach ($source as $name => $entry) {
            if (! is_array($entry) || ! isset($target[$name])) {
                continue;
            }
            $target[$name] = [
                'file' => (string) ($entry['file'] ?? ''),
                'anim_in' => (string) ($entry['anim_in'] ?? ''),
                'duration_ms' => (int) ($entry['duration_ms'] ?? 0),
                'delay_ms' => (int) ($entry['delay_ms'] ?? 0),
            ];
        }
    }

    public function toManifestPatch(): array
    {
        // Default
        $defaultLayout = [];

        if ($this->bgFile !== '') {
            $defaultLayout['background'] = [
                'file' => $this->bgFile,
                'opacity' => $this->bgOpacity,
                'fit' => $this->bgFit,
            ];
        }

        $kept = $this->buildSlotsPatch($this->slots);
        if ($kept !== []) {
            $defaultLayout['slots'] = $kept;
        }

        if ($this->lottieFile !== '') {
            $defaultLayout['lottie'] = [
                'file' => $this->lottieFile,
                'placement' => $this->lottiePlacement,
                'loop' => $this->lottieLoop,
                'size' => $this->lottieSize,
            ];
        }

        // Per-page overrides
        $pagesPatch = [];
        foreach ($this->pages as $type => $pageData) {
            $pageSlots = $this->buildSlotsPatch((array) ($pageData['slots'] ?? []));
            if ($pageSlots !== []) {
                $pagesPatch[$type] = ['slots' => $pageSlots];
            }
        }

        return [
            'layout' => [
                'default' => $defaultLayout,
                'pages' => $pagesPatch !== [] ? $pagesPatch : (object) [],
            ],
        ];
    }

    /**
     * Strip empty slot entries and compose the manifest-shape payload.
     *
     * @param  array<string, array<string, mixed>>  $slotMap
     * @return array<string, array<string, mixed>>
     */
    private function buildSlotsPatch(array $slotMap): array
    {
        $kept = [];
        foreach ($slotMap as $name => $entry) {
            $file = (string) ($entry['file'] ?? '');
            if ($file === '') {
                continue;
            }
            $slotEntry = ['file' => $file];

            $anim = (string) ($entry['anim_in'] ?? '');
            if ($anim !== '') {
                $slotEntry['anim_in'] = $anim;
            }

            $duration = (int) ($entry['duration_ms'] ?? 0);
            if ($duration > 0) {
                $slotEntry['duration_ms'] = $duration;
            }

            $delay = (int) ($entry['delay_ms'] ?? 0);
            if ($delay > 0) {
                $slotEntry['delay_ms'] = $delay;
            }

            $kept[$name] = $slotEntry;
        }

        return $kept;
    }

    /** @return list<string> */
    public static function slotKeys(): array
    {
        return ThemeRegistry::SLOT_KEYS;
    }

    /** @return list<string> */
    public static function animOptions(): array
    {
        return ThemeRegistry::ANIM_PRESETS;
    }

    /** @return list<string> */
    public static function bgFitOptions(): array
    {
        return ThemeRegistry::BG_FITS;
    }

    /** @return list<string> */
    public static function lottiePlacementOptions(): array
    {
        return ThemeRegistry::LOTTIE_PLACEMENTS;
    }

    /** @return list<string> */
    public static function lottieSizeOptions(): array
    {
        return ThemeRegistry::LOTTIE_SIZES;
    }

    /** @return list<string> */
    public static function sectionTypes(): array
    {
        return self::SECTION_TYPES;
    }

    /**
     * Slot grouped by category for UI rendering.
     *
     * @return array<string, list<string>>
     */
    public static function slotGroups(): array
    {
        return [
            'Sudut (Corner)' => ['corner-tl', 'corner-tr', 'corner-bl', 'corner-br'],
            'Ujung (Edge)' => ['edge-top', 'edge-bottom'],
            'Sisi (Side)' => ['side-tc', 'side-bc', 'side-lc', 'side-rc'],
            'Tengah (Middle)' => ['middle'],
        ];
    }

    /**
     * Human-readable label per slot key.
     *
     * @return array<string, string>
     */
    public static function slotLabels(): array
    {
        return [
            'corner-tl' => 'Top Left',
            'corner-tr' => 'Top Right',
            'corner-bl' => 'Bottom Left',
            'corner-br' => 'Bottom Right',
            'edge-top' => 'Edge Top (full-width)',
            'edge-bottom' => 'Edge Bottom (full-width)',
            'side-tc' => 'Top Center',
            'side-bc' => 'Bottom Center',
            'side-lc' => 'Left Center',
            'side-rc' => 'Right Center',
            'middle' => 'Tengah Halaman',
        ];
    }
}
