<?php

declare(strict_types=1);

use App\Livewire\Admin\ThemeList;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders all themes by default with tier counts', function () {
    Livewire::test(ThemeList::class)
        ->assertSet('tier', 'all')
        ->assertSet('sort', 'name')
        ->assertSee('Themes Library');
});

it('filters by tier=premium chip', function () {
    $component = Livewire::test(ThemeList::class)
        ->set('tier', 'premium');

    // All themes shown should be premium — verify by checking that the
    // computed theme list contains only premium ones.
    $themes = $component->viewData('themes');
    foreach ($themes as $row) {
        expect($row['theme']->isPremium)->toBeTrue();
    }
});

it('filters by tier=free chip', function () {
    $component = Livewire::test(ThemeList::class)
        ->set('tier', 'free');

    $themes = $component->viewData('themes');
    foreach ($themes as $row) {
        expect($row['theme']->isPremium)->toBeFalse();
    }
});

it('sorts by name A-Z by default', function () {
    $themes = Livewire::test(ThemeList::class)->viewData('themes');

    $names = $themes->pluck('theme.name')->map(fn ($n) => strtolower($n))->all();
    $sorted = $names;
    sort($sorted);
    expect($names)->toBe($sorted);
});

it('sorts by popular when invitations attached', function () {
    // Find any existing themes; create invitations against them
    $themes = Livewire::test(ThemeList::class)->viewData('themes');
    if ($themes->count() < 2) {
        $this->markTestSkipped('Need at least 2 themes for popular-sort test.');
    }

    $first = $themes[0]['theme']->slug;
    $second = $themes[1]['theme']->slug;
    Invitation::factory()->count(3)->create(['theme_slug' => $second]);
    Invitation::factory()->count(1)->create(['theme_slug' => $first]);

    $sorted = Livewire::test(ThemeList::class)
        ->set('sort', 'popular')
        ->viewData('themes');

    // The second theme (3 invitations) should be ahead of first (1).
    $orderedSlugs = $sorted->pluck('theme.slug')->all();
    $secondPos = array_search($second, $orderedSlugs, true);
    $firstPos = array_search($first, $orderedSlugs, true);
    expect($secondPos)->toBeLessThan($firstPos);
});

it('counts invitations per theme in metadata', function () {
    $themes = Livewire::test(ThemeList::class)->viewData('themes');
    if ($themes->isEmpty()) {
        $this->markTestSkipped('No themes installed.');
    }

    $slug = $themes[0]['theme']->slug;
    Invitation::factory()->count(2)->create(['theme_slug' => $slug]);

    $refreshed = Livewire::test(ThemeList::class)->viewData('themes');
    $row = $refreshed->firstWhere('theme.slug', $slug);
    expect($row['invitationCount'])->toBe(2);
});
