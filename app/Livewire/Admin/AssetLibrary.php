<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Themes\AssetUsageAnalyzer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

final class AssetLibrary extends Component
{
    use WithFileUploads;

    public string $slug = '';

    #[Rule(['uploadedFiles.*' => 'file|max:10240'])]
    public array $uploadedFiles = [];

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    public bool $showDeleteModal = false;

    public string $deleteTarget = '';

    public array $deleteUsages = [];

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function uploadFiles(): void
    {
        $this->validate();

        // Single source of truth: the `theme_assets` disk (rooted at
        // resource_path('themes')). Existence check + makeDirectory + write
        // all go through it so behavior stays consistent under test rebinding.
        $disk = Storage::disk('theme_assets');
        $assetsDir = "{$this->slug}/assets";

        if (! $disk->directoryExists($assetsDir)) {
            $disk->makeDirectory($assetsDir);
        }

        $count = 0;

        foreach ($this->uploadedFiles as $file) {
            $original = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());

            if (! in_array($ext, ['webp', 'svg', 'png', 'jpg', 'jpeg', 'json'], true)) {
                continue;
            }

            // Sanitize base name: lowercase, alphanumeric + hyphens
            $base = Str::slug(pathinfo($original, PATHINFO_FILENAME), '-');
            $filename = $base.'.'.$ext;

            // Resolve collision against existing files on the disk
            $candidate = $filename;
            $i = 2;

            while ($disk->exists($assetsDir.'/'.$candidate)) {
                $candidate = $base.'-'.$i.'.'.$ext;
                $i++;
            }

            // storeAs() goes through Livewire's TemporaryUploadedFile path which
            // copies via stream — works on Windows where Symfony's move_uploaded_file()
            // would silently fail because the temp file isn't in $_FILES.
            $file->storeAs($assetsDir, $candidate, 'theme_assets');
            $count++;
        }

        if ($count > 0) {
            Artisan::call('themes:publish-assets', ['slug' => $this->slug]);
            $this->dispatch('assets-updated');
        }

        $this->uploadedFiles = [];
        $this->flash("{$count} file berhasil diupload.", 'success');
    }

    public function confirmDelete(string $filename): void
    {
        // Prevent path traversal
        if (basename($filename) !== $filename) {
            return;
        }

        $analyzer = app(AssetUsageAnalyzer::class);
        $this->deleteTarget = $filename;
        $this->deleteUsages = $analyzer->whereUsed($this->slug, $filename);
        $this->showDeleteModal = true;
    }

    public function deleteAsset(): void
    {
        $filename = $this->deleteTarget;

        if (basename($filename) !== $filename || $filename === '') {
            $this->closeDeleteModal();

            return;
        }

        $sourcePath = resource_path("themes/{$this->slug}/assets/{$filename}");
        $publicPath = public_path("themes/{$this->slug}/{$filename}");

        if (File::exists($sourcePath)) {
            File::delete($sourcePath);
        }

        if (File::exists($publicPath)) {
            File::delete($publicPath);
        }

        $this->closeDeleteModal();
        $this->dispatch('assets-updated');
        $this->flash("File '{$filename}' dihapus.", 'success');
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTarget = '';
        $this->deleteUsages = [];
    }

    public function publishAssets(): void
    {
        Artisan::call('themes:publish-assets', ['slug' => $this->slug]);
        $this->flash('Assets dipublish ke public/themes.', 'success');
    }

    private function flash(string $message, string $type): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function render(): View
    {
        $analyzer = app(AssetUsageAnalyzer::class);
        $assets = $analyzer->listAssets($this->slug);

        return view('livewire.admin.asset-library', ['assets' => $assets]);
    }
}
