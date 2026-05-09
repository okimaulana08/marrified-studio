<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Themes\ThemeRegistry;
use App\Services\Themes\ThemeWriter;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // All admin theme routes are gated by `auth + role:admin` since Phase 0.
    $this->actingAs(User::factory()->admin()->create());

    $this->tmp = storage_path('framework/testing/admin-'.uniqid());
    File::makeDirectory($this->tmp.'/watercolor-lush/assets', 0755, true);

    $manifest = [
        'slug' => 'watercolor-lush',
        'name' => 'Watercolor Lush',
        'is_premium' => true,
        'default_palette' => ['primary' => '#5d8068', 'accent' => '#c9a96e', 'accent2' => '#3d5a47', 'bg' => '#eef2ea', 'ink' => '#2a3d31', 'muted' => '#6f7e72'],
        'default_fonts' => ['display' => 'Playfair Display', 'body' => 'Lato', 'script' => 'Petit Formal Script'],
        'default_section_variants' => ['cover' => 'arch', 'quotes' => 'default', 'couple' => 'side-by-side', 'event' => 'card', 'gallery' => 'grid', 'gift' => 'cashless-modal', 'rsvp' => 'default', 'guestbook' => 'default'],
        'layout' => ['default' => ['slots' => []], 'pages' => []],
    ];

    File::put($this->tmp.'/watercolor-lush/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    File::put($this->tmp.'/watercolor-lush/assets/preview.webp', 'fake');

    // Swap singletons to use temp directory
    app()->singleton(ThemeRegistry::class, fn () => new ThemeRegistry($this->tmp));
    app()->singleton(ThemeWriter::class, fn ($app) => new ThemeWriter(
        $app->make(ThemeRegistry::class),
        $this->tmp
    ));
});

afterEach(function () {
    File::deleteDirectory($this->tmp);
});

it('returns 200 on admin theme index', function () {
    $this->get(route('admin.themes.index'))->assertOk();
});

it('returns 200 on admin theme create', function () {
    $this->get(route('admin.themes.create'))->assertOk();
});

it('returns 200 on admin theme edit for existing slug', function () {
    $this->get(route('admin.themes.edit', 'watercolor-lush'))->assertOk();
});

it('returns 404 for non-existent theme slug', function () {
    $this->get(route('admin.themes.edit', 'ghost-theme'))->assertNotFound();
});

it('returns 200 on admin preview', function () {
    $this->get(route('admin.themes.preview', 'watercolor-lush'))->assertOk();
});

it('admin index does not collide with public invitation route', function () {
    // public route /{slug}/{token?} should not match /admin/themes
    $this->get('/admin/themes')->assertOk();
});
