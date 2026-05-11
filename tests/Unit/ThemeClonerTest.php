<?php

declare(strict_types=1);

use App\Services\Themes\ThemeCloner;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tmp = storage_path('framework/testing/cloner-'.uniqid());
    File::makeDirectory($this->tmp, 0755, true);
    $this->registry = new ThemeRegistry($this->tmp);
    $this->cloner = new ThemeCloner($this->registry, $this->tmp);

    // Create a source theme with assets
    $sourceDir = $this->tmp.'/source-theme';
    File::makeDirectory($sourceDir.'/assets', 0755, true);
    File::put($sourceDir.'/manifest.json', json_encode([
        'slug' => 'source-theme',
        'name' => 'Source Theme',
        'is_premium' => false,
        'default_palette' => ['primary' => '#5d8068'],
        'default_fonts' => ['display' => 'Playfair Display'],
        'default_section_variants' => ['cover' => 'arch'],
        'decorations' => [],
    ], JSON_PRETTY_PRINT));
    File::put($sourceDir.'/assets/flower.webp', 'fake-webp-data');
});

afterEach(function () {
    File::deleteDirectory($this->tmp);
});

it('copies manifest and assets to new slug', function () {
    $this->cloner->clone('source-theme', 'target-theme');

    $targetDir = $this->tmp.'/target-theme';

    expect(File::isDirectory($targetDir))->toBeTrue()
        ->and(File::exists($targetDir.'/manifest.json'))->toBeTrue()
        ->and(File::exists($targetDir.'/assets/flower.webp'))->toBeTrue();
});

it('updates slug and name in cloned manifest', function () {
    $this->cloner->clone('source-theme', 'my-copy');

    $manifest = json_decode(File::get($this->tmp.'/my-copy/manifest.json'), true);

    expect($manifest['slug'])->toBe('my-copy')
        ->and($manifest['name'])->toContain('(Copy)');
});

it('publishes cloned assets to public/themes inline', function () {
    $this->cloner->clone('source-theme', 'published-copy');

    $publicPath = public_path('themes/published-copy/flower.webp');
    expect(File::exists($publicPath))->toBeTrue();

    // Cleanup so the next test run isn't polluted
    File::deleteDirectory(public_path('themes/published-copy'));
});

it('rejects clone to existing slug', function () {
    File::makeDirectory($this->tmp.'/existing-theme', 0755, true);

    expect(fn () => $this->cloner->clone('source-theme', 'existing-theme'))
        ->toThrow(RuntimeException::class, 'already exists');
});

it('rejects invalid target slug format', function () {
    expect(fn () => $this->cloner->clone('source-theme', 'INVALID Slug'))
        ->toThrow(RuntimeException::class, 'Invalid target slug');
});

it('rejects clone from non-existent source', function () {
    expect(fn () => $this->cloner->clone('ghost-theme', 'target-theme'))
        ->toThrow(RuntimeException::class, 'not found');
});
