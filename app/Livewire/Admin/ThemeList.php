<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Invitation;
use App\Services\Themes\AssetUsageAnalyzer;
use App\Services\Themes\ThemeCloner;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

final class ThemeList extends Component
{
    public string $search = '';

    // Clone modal state
    public bool $showCloneModal = false;

    public string $cloneSourceSlug = '';

    #[Validate('required|regex:/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/')]
    public string $cloneTargetSlug = '';

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    public function openCloneModal(string $slug): void
    {
        $this->cloneSourceSlug = $slug;
        $this->cloneTargetSlug = $slug.'-copy';
        $this->showCloneModal = true;
        $this->resetErrorBag();
    }

    public function closeCloneModal(): void
    {
        $this->showCloneModal = false;
        $this->cloneSourceSlug = '';
        $this->cloneTargetSlug = '';
    }

    public function confirmClone(): void
    {
        $this->validate();

        try {
            app(ThemeCloner::class)->clone($this->cloneSourceSlug, $this->cloneTargetSlug);
        } catch (RuntimeException $e) {
            $this->addError('cloneTargetSlug', $e->getMessage());

            return;
        }

        $this->closeCloneModal();
        $this->flashMessage = "Tema '{$this->cloneSourceSlug}' berhasil diduplikasi.";
        $this->flashType = 'success';

        $this->redirect(route('admin.themes.edit', $this->cloneTargetSlug));
    }

    public function render(): View
    {
        $registry = app(ThemeRegistry::class);
        $analyzer = app(AssetUsageAnalyzer::class);

        $themes = collect($registry->all())
            ->when($this->search !== '', fn ($c) => $c->filter(
                fn ($t) => str_contains(strtolower($t->name), strtolower($this->search))
                    || str_contains(strtolower($t->slug), strtolower($this->search))
            ))
            ->map(function ($theme) use ($analyzer) {
                $assetCount = count($analyzer->listAssets($theme->slug));
                $previewInvitation = Invitation::query()->where('theme_slug', $theme->slug)->first();

                return [
                    'theme' => $theme,
                    'assetCount' => $assetCount,
                    'previewUrl' => $previewInvitation
                        ? route('public.invitation', $previewInvitation->slug)
                        : route('admin.themes.preview', $theme->slug),
                ];
            })
            ->values();

        return view('livewire.admin.theme-list', ['themes' => $themes]);
    }
}
