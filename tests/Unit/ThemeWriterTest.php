<?php

declare(strict_types=1);

use App\Services\Themes\ThemeRegistry;
use App\Services\Themes\ThemeWriter;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tmp = storage_path('framework/testing/writer-'.uniqid());
    File::makeDirectory($this->tmp, 0755, true);
    $this->registry = new ThemeRegistry($this->tmp);
    $this->writer = new ThemeWriter($this->registry, $this->tmp);
});

afterEach(function () {
    File::deleteDirectory($this->tmp);
});

it('creates theme directory with skeleton manifest', function () {
    $this->writer->createTheme('new-theme', 'New Theme');

    $dir = $this->tmp.'/new-theme';
    $manifestPath = $dir.'/manifest.json';

    expect(File::isDirectory($dir))->toBeTrue()
        ->and(File::isDirectory($dir.'/assets'))->toBeTrue()
        ->and(File::exists($manifestPath))->toBeTrue();

    $manifest = json_decode(File::get($manifestPath), true);

    expect($manifest['slug'])->toBe('new-theme')
        ->and($manifest['name'])->toBe('New Theme')
        ->and($manifest['is_premium'])->toBeFalse()
        ->and($manifest['default_palette'])->toHaveKey('primary')
        ->and($manifest['default_fonts'])->toHaveKey('display');
});

it('creates premium theme when flag is set', function () {
    $this->writer->createTheme('premium-one', 'Premium Theme', true);

    $manifest = json_decode(File::get($this->tmp.'/premium-one/manifest.json'), true);

    expect($manifest['is_premium'])->toBeTrue();
});

it('rejects invalid slug format', function () {
    expect(fn () => $this->writer->createTheme('UPPER-CASE', 'Test'))->toThrow(RuntimeException::class, 'Invalid slug')
        ->and(fn () => $this->writer->createTheme('ab', 'Too Short'))->toThrow(RuntimeException::class, 'Invalid slug')
        ->and(fn () => $this->writer->createTheme('has spaces', 'Test'))->toThrow(RuntimeException::class, 'Invalid slug');
});

it('rejects duplicate slug', function () {
    $this->writer->createTheme('unique-theme', 'Theme');

    expect(fn () => $this->writer->createTheme('unique-theme', 'Duplicate'))->toThrow(RuntimeException::class, 'already exists');
});

it('writes manifest atomically via tmp + rename', function () {
    $this->writer->createTheme('atomic-test', 'Atomic');

    // Patch the palette
    $this->writer->writeManifest('atomic-test', ['default_palette' => ['primary' => '#ff0000']]);

    $manifest = json_decode(File::get($this->tmp.'/atomic-test/manifest.json'), true);

    expect($manifest['default_palette']['primary'])->toBe('#ff0000')
        ->and($manifest['slug'])->toBe('atomic-test'); // slug never drifts

    // No .tmp files should linger
    $tmpFiles = File::glob($this->tmp.'/atomic-test/manifest.json.tmp.*');
    expect($tmpFiles)->toBeEmpty();
});

it('flushes registry cache after write', function () {
    $this->writer->createTheme('cache-test', 'Cache Theme');

    // Prime the cache
    $this->registry->all();

    // Change name without going through writer (simulate external edit)
    $path = $this->tmp.'/cache-test/manifest.json';
    $data = json_decode(File::get($path), true);
    $data['name'] = 'Updated Name';
    File::put($path, json_encode($data));

    // Writer flush should invalidate
    $this->writer->writeManifest('cache-test', ['name' => 'Updated Name']);

    $theme = $this->registry->find('cache-test');
    expect($theme->name)->toBe('Updated Name');
});

it('merges patch with existing manifest instead of overwriting', function () {
    $this->writer->createTheme('merge-test', 'Merge Theme');
    $this->writer->writeManifest('merge-test', ['description' => 'My description']);
    $this->writer->writeManifest('merge-test', ['default_palette' => ['primary' => '#aabbcc']]);

    $manifest = json_decode(File::get($this->tmp.'/merge-test/manifest.json'), true);

    // Both patches preserved
    expect($manifest['description'])->toBe('My description')
        ->and($manifest['default_palette']['primary'])->toBe('#aabbcc');
});

it('strips empty layout entries before writing', function () {
    $this->writer->createTheme('strip-test', 'Strip');

    $this->writer->writeManifest('strip-test', [
        'layout' => [
            'default' => [
                'background' => ['file' => '', 'opacity' => 0.5, 'fit' => 'cover'],
                'slots' => [
                    'middle' => ['file' => '', 'anim_in' => 'fade-in'],
                    'edge-top' => ['file' => 'florals.webp', 'anim_in' => 'fade-down'],
                ],
                'lottie' => ['file' => '', 'placement' => 'center'],
            ],
            'pages' => [
                'cover' => [
                    'slots' => [
                        'middle' => ['file' => '', 'anim_in' => 'scale-in'],
                    ],
                ],
            ],
        ],
    ]);

    $manifest = json_decode(File::get($this->tmp.'/strip-test/manifest.json'), true);

    expect($manifest['layout']['default'])->not->toHaveKey('background')
        ->and($manifest['layout']['default'])->not->toHaveKey('lottie')
        ->and($manifest['layout']['default']['slots'])->toHaveCount(1)
        ->and($manifest['layout']['default']['slots'])->toHaveKey('edge-top')
        ->and($manifest['layout']['pages'] ?? [])->not->toHaveKey('cover');
});

it('reads manifest array for editing', function () {
    $this->writer->createTheme('read-test', 'Read Theme');
    $this->writer->writeManifest('read-test', ['description' => 'For reading']);

    $array = $this->writer->readManifestArray('read-test');

    expect($array)->toBeArray()
        ->and($array['slug'])->toBe('read-test')
        ->and($array['description'])->toBe('For reading');
});

it('returns empty array for non-existent theme', function () {
    expect($this->writer->readManifestArray('does-not-exist'))->toBe([]);
});
