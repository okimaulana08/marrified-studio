<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Invitation;
use App\Services\Themes\AssetUsageAnalyzer;
use App\Services\Themes\ThemeCloner;
use App\Services\Themes\ThemeRegistry;
use App\Services\Themes\ThemeWriter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

final class ThemeList extends Component
{
    public string $search = '';

    /** all | free | premium */
    public string $tier = 'all';

    /** name | recent | popular */
    public string $sort = 'name';

    // Clone modal state
    public bool $showCloneModal = false;

    public string $cloneSourceSlug = '';

    #[Validate('required|regex:/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/')]
    public string $cloneTargetSlug = '';

    // Delete modal state — admin must type the slug exactly to confirm.
    public bool $showDeleteModal = false;

    public string $deleteTargetSlug = '';

    public string $deleteTargetName = '';

    public int $deleteTargetUsage = 0;

    public string $deleteConfirmInput = '';

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    public function openDeleteModal(string $slug): void
    {
        $theme = app(ThemeRegistry::class)->find($slug);
        if ($theme === null) {
            $this->flashMessage = "Tema '{$slug}' tidak ditemukan.";
            $this->flashType = 'error';

            return;
        }

        $this->deleteTargetSlug = $slug;
        $this->deleteTargetName = $theme->name;
        $this->deleteTargetUsage = Invitation::query()->where('theme_slug', $slug)->count();
        $this->deleteConfirmInput = '';
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetSlug = '';
        $this->deleteTargetName = '';
        $this->deleteTargetUsage = 0;
        $this->deleteConfirmInput = '';
    }

    public function confirmDelete(): void
    {
        if ($this->deleteTargetSlug === '') {
            return;
        }

        if ($this->deleteTargetUsage > 0) {
            $this->flashMessage = "Tidak bisa hapus: {$this->deleteTargetUsage} invitation masih pakai tema ini.";
            $this->flashType = 'error';

            return;
        }

        if ($this->deleteConfirmInput !== $this->deleteTargetSlug) {
            $this->addError('deleteConfirmInput', 'Slug tidak cocok. Ketik ulang persis.');

            return;
        }

        try {
            app(ThemeWriter::class)->deleteTheme($this->deleteTargetSlug);
        } catch (RuntimeException $e) {
            $this->flashMessage = $e->getMessage();
            $this->flashType = 'error';

            return;
        }

        $deletedSlug = $this->deleteTargetSlug;
        $this->closeDeleteModal();

        $this->flashMessage = "Tema '{$deletedSlug}' dihapus permanen.";
        $this->flashType = 'success';
    }

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

        // Snapshot the slugs BEFORE resetting the modal state — otherwise
        // closeCloneModal() blanks $cloneTargetSlug and route() throws.
        $newSlug = $this->cloneTargetSlug;
        $sourceSlug = $this->cloneSourceSlug;

        $this->closeCloneModal();
        $this->flashMessage = "Tema '{$sourceSlug}' berhasil diduplikasi.";
        $this->flashType = 'success';

        $this->redirect(route('admin.themes.edit', $newSlug));
    }

    public function render(): View
    {
        $registry = app(ThemeRegistry::class);
        $analyzer = app(AssetUsageAnalyzer::class);

        // Counts of invitations per theme (drives "popular" sort + per-card stat).
        $invitationCounts = Invitation::query()
            ->selectRaw('theme_slug, COUNT(*) as cnt')
            ->groupBy('theme_slug')
            ->pluck('cnt', 'theme_slug')
            ->all();

        $themes = collect($registry->all())
            ->when($this->search !== '', fn ($c) => $c->filter(
                fn ($t) => str_contains(strtolower($t->name), strtolower($this->search))
                    || str_contains(strtolower($t->slug), strtolower($this->search))
            ))
            ->when($this->tier === 'free', fn ($c) => $c->filter(fn ($t) => ! $t->isPremium))
            ->when($this->tier === 'premium', fn ($c) => $c->filter(fn ($t) => $t->isPremium))
            ->map(function ($theme) use ($analyzer, $invitationCounts) {
                $assetCount = count($analyzer->listAssets($theme->slug));
                $previewInvitation = Invitation::query()->where('theme_slug', $theme->slug)->first();
                $manifestPath = resource_path("themes/{$theme->slug}/manifest.json");
                $updatedAt = file_exists($manifestPath) ? filemtime($manifestPath) : 0;

                return [
                    'theme' => $theme,
                    'assetCount' => $assetCount,
                    'invitationCount' => (int) ($invitationCounts[$theme->slug] ?? 0),
                    'updatedAt' => $updatedAt,
                    'previewUrl' => $previewInvitation
                        ? route('public.invitation', $previewInvitation->slug)
                        : route('admin.themes.preview', $theme->slug),
                ];
            })
            ->sortBy(match ($this->sort) {
                'recent' => fn ($r) => -$r['updatedAt'],
                'popular' => fn ($r) => -$r['invitationCount'],
                default => fn ($r) => strtolower($r['theme']->name),
            })
            ->values();

        $totalAll = count($registry->all());
        $totalPremium = collect($registry->all())->filter(fn ($t) => $t->isPremium)->count();
        $totalFree = $totalAll - $totalPremium;

        return view('livewire.admin.theme-list', [
            'themes' => $themes,
            'tierCounts' => ['all' => $totalAll, 'free' => $totalFree, 'premium' => $totalPremium],
        ]);
    }
}
