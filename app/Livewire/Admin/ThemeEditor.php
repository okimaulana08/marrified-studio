<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\Admin\Forms\BasicInfoForm;
use App\Livewire\Admin\Forms\FontsForm;
use App\Livewire\Admin\Forms\LayoutForm;
use App\Livewire\Admin\Forms\PaletteForm;
use App\Livewire\Admin\Forms\VariantsForm;
use App\Services\Themes\AssetUsageAnalyzer;
use App\Services\Themes\ThemeValidator;
use App\Services\Themes\ThemeWriter;
use App\Services\Themes\VariantScanner;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use RuntimeException;

final class ThemeEditor extends Component
{
    private const FORM_NAMES = ['basic', 'palette', 'fonts', 'variants', 'layout'];

    public string $slug = '';

    public bool $isNew = true;

    public BasicInfoForm $basic;

    public PaletteForm $palette;

    public FontsForm $fonts;

    public VariantsForm $variants;

    public LayoutForm $layout;

    /** Tracks which form objects have unsaved changes. */
    public array $dirty = [];

    /** Incremented after each save to force iframe refresh. */
    public int $previewKey = 0;

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    public function mount(string $slug = ''): void
    {
        $this->slug = $slug;
        $this->isNew = $slug === '';

        if (! $this->isNew) {
            $manifest = app(ThemeWriter::class)->readManifestArray($slug);
            $this->populateFromManifest($manifest);
        }
    }

    public function updated(string $name): void
    {
        $formName = explode('.', $name)[0];

        if (in_array($formName, self::FORM_NAMES, true)) {
            $this->dirty[$formName] = true;
        }
    }

    public function save(): void
    {
        $writer = app(ThemeWriter::class);
        $validator = app(ThemeValidator::class);

        try {
            if ($this->isNew) {
                $this->basic->validate();

                if (is_dir(resource_path("themes/{$this->basic->slug}"))) {
                    $this->addError('basic.slug', "Slug '{$this->basic->slug}' sudah dipakai.");

                    return;
                }

                $writer->createTheme($this->basic->slug, $this->basic->name, $this->basic->isPremium);
                $this->slug = $this->basic->slug;
                $this->isNew = false;
                $this->dirty['basic'] = true;
            }

            // Fall back to patching every form when dirty tracking is empty
            // (can happen when wire:model.live on Form object properties doesn't
            // trigger the parent updated() hook in Livewire 4.3).
            $formsToPatch = array_keys($this->dirty) ?: self::FORM_NAMES;

            // Per-form field validation (skip 'layout' and 'variants' — their
            // cross-field validation runs via ThemeValidator after toManifestPatch,
            // and Livewire throws MissingRulesException for forms without rules).
            $skipValidate = ['layout', 'variants'];
            foreach ($formsToPatch as $formName) {
                if (in_array($formName, $skipValidate, true)) {
                    continue;
                }
                $this->{$formName}->validate();
            }

            $patch = [];

            foreach ($formsToPatch as $formName) {
                $patch = array_merge($patch, $this->{$formName}->toManifestPatch());
            }

            $validator->validate($this->slug, $patch);

            $writer->writeManifest($this->slug, $patch);

            Artisan::call('themes:publish-assets', ['slug' => $this->slug]);

            $this->dirty = [];
            $this->previewKey++;
            $this->flash('Tema disimpan.', 'success');
            $this->dispatch('theme-saved');

        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function discardChanges(): void
    {
        $manifest = app(ThemeWriter::class)->readManifestArray($this->slug);
        $this->populateFromManifest($manifest);
        $this->dirty = [];
        $this->flash('Perubahan dibatalkan.', 'info');
        $this->dispatch('theme-discarded');
    }

    public function applyPalettePreset(string $key): void
    {
        $this->palette->applyPreset($key);
        $this->dirty['palette'] = true;
    }

    private function populateFromManifest(array $manifest): void
    {
        $this->basic->fillFromManifest($manifest);
        $this->palette->fillFromManifest((array) ($manifest['default_palette'] ?? []));
        $this->fonts->fillFromManifest((array) ($manifest['default_fonts'] ?? []));
        $this->variants->fillFromManifest((array) ($manifest['default_section_variants'] ?? []));
        $this->layout->fillFromManifest((array) ($manifest['layout'] ?? []));
    }

    private function flash(string $message, string $type): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function render(): View
    {
        $variantOptions = app(VariantScanner::class)->all();
        $availableAssets = $this->slug !== ''
            ? app(AssetUsageAnalyzer::class)->listAssets($this->slug)
            : [];

        return view('livewire.admin.theme-editor', [
            'variantOptions' => $variantOptions,
            'availableAssets' => $availableAssets,
            'palettePresets' => PaletteForm::PRESETS,
            'slotGroups' => LayoutForm::slotGroups(),
            'slotLabels' => LayoutForm::slotLabels(),
            'animOptions' => LayoutForm::animOptions(),
            'bgFitOptions' => LayoutForm::bgFitOptions(),
            'lottiePlacementOptions' => LayoutForm::lottiePlacementOptions(),
            'lottieSizeOptions' => LayoutForm::lottieSizeOptions(),
        ]);
    }
}
