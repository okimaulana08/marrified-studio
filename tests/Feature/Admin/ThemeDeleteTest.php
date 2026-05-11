<?php

declare(strict_types=1);

use App\Livewire\Admin\ThemeList;
use App\Models\Invitation;
use App\Models\User;
use App\Services\Themes\ThemeCloner;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);

    // Build a disposable cloned theme so we never touch the production
    // watercolor-lush source files.
    app(ThemeCloner::class)->clone('watercolor-lush', 'unit-delete-target');
});

afterEach(function () {
    foreach (['unit-delete-target'] as $slug) {
        File::deleteDirectory(resource_path("themes/{$slug}"));
        File::deleteDirectory(public_path("themes/{$slug}"));
    }
});

it('opens the delete modal with theme metadata', function () {
    Livewire::test(ThemeList::class)
        ->call('openDeleteModal', 'unit-delete-target')
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteTargetSlug', 'unit-delete-target')
        ->assertSet('deleteTargetUsage', 0);
});

it('requires the confirm input to match slug exactly', function () {
    Livewire::test(ThemeList::class)
        ->call('openDeleteModal', 'unit-delete-target')
        ->set('deleteConfirmInput', 'wrong-slug')
        ->call('confirmDelete')
        ->assertHasErrors(['deleteConfirmInput'])
        ->assertSet('showDeleteModal', true);

    expect(File::isDirectory(resource_path('themes/unit-delete-target')))->toBeTrue();
});

it('deletes the theme when slug matches exactly', function () {
    Livewire::test(ThemeList::class)
        ->call('openDeleteModal', 'unit-delete-target')
        ->set('deleteConfirmInput', 'unit-delete-target')
        ->call('confirmDelete')
        ->assertSet('flashType', 'success')
        ->assertSet('showDeleteModal', false);

    expect(File::isDirectory(resource_path('themes/unit-delete-target')))->toBeFalse()
        ->and(File::isDirectory(public_path('themes/unit-delete-target')))->toBeFalse();
});

it('blocks delete when invitations still reference the theme', function () {
    Invitation::factory()->create(['theme_slug' => 'unit-delete-target']);

    Livewire::test(ThemeList::class)
        ->call('openDeleteModal', 'unit-delete-target')
        ->assertSet('deleteTargetUsage', 1)
        ->set('deleteConfirmInput', 'unit-delete-target')
        ->call('confirmDelete')
        ->assertSet('flashType', 'error');

    expect(File::isDirectory(resource_path('themes/unit-delete-target')))->toBeTrue();
});
