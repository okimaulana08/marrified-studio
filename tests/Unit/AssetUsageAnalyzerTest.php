<?php

declare(strict_types=1);

use App\Services\Themes\AssetUsageAnalyzer;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tmp = storage_path('framework/testing/analyzer-'.uniqid());
    File::makeDirectory($this->tmp.'/my-theme/assets', 0755, true);
    $this->analyzer = new AssetUsageAnalyzer($this->tmp);

    File::put($this->tmp.'/my-theme/manifest.json', json_encode([
        'slug' => 'my-theme',
        'name' => 'My Theme',
        'decorations' => [
            'background' => ['file' => 'sky.webp', 'opacity' => 0.5],
            'sections' => [
                'cover' => ['frame' => ['file' => 'arch.webp'], 'tossed' => ['file' => 'flowers.webp', 'slot' => 'bottom']],
                'quotes' => ['icon' => ['file' => 'moon.svg']],
            ],
        ],
    ]));

    foreach (['sky.webp', 'arch.webp', 'flowers.webp', 'moon.svg', 'unused.webp'] as $f) {
        File::put($this->tmp.'/my-theme/assets/'.$f, '');
    }
});

afterEach(function () {
    File::deleteDirectory($this->tmp);
});

it('finds background reference', function () {
    $usages = $this->analyzer->whereUsed('my-theme', 'sky.webp');
    expect($usages)->toContain('decorations.background');
});

it('finds section decoration references', function () {
    expect($this->analyzer->whereUsed('my-theme', 'arch.webp'))->toContain('sections.cover.frame')
        ->and($this->analyzer->whereUsed('my-theme', 'flowers.webp'))->toContain('sections.cover.tossed')
        ->and($this->analyzer->whereUsed('my-theme', 'moon.svg'))->toContain('sections.quotes.icon');
});

it('returns empty for unused asset', function () {
    expect($this->analyzer->whereUsed('my-theme', 'unused.webp'))->toBe([]);
});

it('reports isUsed correctly', function () {
    expect($this->analyzer->isUsed('my-theme', 'sky.webp'))->toBeTrue()
        ->and($this->analyzer->isUsed('my-theme', 'unused.webp'))->toBeFalse();
});

it('lists all assets in assets folder', function () {
    $assets = $this->analyzer->listAssets('my-theme');

    expect($assets)->toContain('sky.webp')
        ->and($assets)->toContain('arch.webp')
        ->and($assets)->toContain('moon.svg')
        ->and($assets)->toContain('unused.webp');
});

it('returns empty list for missing theme', function () {
    expect($this->analyzer->listAssets('ghost-theme'))->toBe([]);
});
