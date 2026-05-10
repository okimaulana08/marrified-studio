<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\Admin\Forms\BasicInfoForm;
use App\Livewire\Admin\Forms\CustomCssForm;
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
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

final class ThemeEditor extends Component
{
    use WithFileUploads;

    private const FORM_NAMES = ['basic', 'palette', 'fonts', 'variants', 'layout', 'customCss'];

    private const PREVIEW_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];

    /** Pending preview-image upload. Cleared after savePreviewImage(). */
    #[Validate('nullable|image|mimes:webp,jpg,jpeg,png|max:2048')]
    public $previewImage = null;

    public string $slug = '';

    public bool $isNew = true;

    public BasicInfoForm $basic;

    public PaletteForm $palette;

    public FontsForm $fonts;

    public VariantsForm $variants;

    public LayoutForm $layout;

    public CustomCssForm $customCss;

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

    public function savePreviewImage(): void
    {
        if ($this->isNew || $this->slug === '') {
            $this->flash('Simpan tema dulu sebelum upload preview.', 'error');

            return;
        }

        try {
            $this->validate(['previewImage' => 'required|image|mimes:webp,jpg,jpeg,png|max:2048']);

            if (! $this->previewImage instanceof TemporaryUploadedFile) {
                return;
            }

            $assetsDir = resource_path("themes/{$this->slug}/assets");
            File::ensureDirectoryExists($assetsDir);

            // Wipe any prior preview.* so the only file left is the new one,
            // matching the lookup order in ThemeAsset::findPreview.
            foreach (self::PREVIEW_EXTENSIONS as $ext) {
                $existing = "{$assetsDir}/preview.{$ext}";
                if (File::exists($existing)) {
                    File::delete($existing);
                }
            }

            $ext = strtolower($this->previewImage->getClientOriginalExtension() ?: 'webp');
            if (! in_array($ext, self::PREVIEW_EXTENSIONS, true)) {
                $ext = 'webp';
            }
            File::copy($this->previewImage->getRealPath(), "{$assetsDir}/preview.{$ext}");

            Artisan::call('themes:publish-assets', ['slug' => $this->slug]);

            $this->previewImage = null;
            $this->previewKey++;
            $this->flash('Preview image diperbarui.', 'success');
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function removePreviewImage(): void
    {
        if ($this->isNew || $this->slug === '') {
            return;
        }

        $assetsDir = resource_path("themes/{$this->slug}/assets");
        $publicDir = public_path("themes/{$this->slug}");

        foreach (self::PREVIEW_EXTENSIONS as $ext) {
            foreach (["{$assetsDir}/preview.{$ext}", "{$publicDir}/preview.{$ext}"] as $path) {
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
        }

        $this->previewImage = null;
        $this->previewKey++;
        $this->flash('Preview image dihapus.', 'success');
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
        $this->customCss->fillFromManifest($manifest);
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
            'animLoopOptions' => LayoutForm::animLoopOptions(),
            'bgFitOptions' => LayoutForm::bgFitOptions(),
            'lottiePlacementOptions' => LayoutForm::lottiePlacementOptions(),
            'lottieSizeOptions' => LayoutForm::lottieSizeOptions(),
        ]);
    }
}
