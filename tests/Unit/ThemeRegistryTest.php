<?php

declare(strict_types=1);

use App\Services\Themes\Theme;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tmp = storage_path('framework/testing/themes-'.uniqid());
    File::makeDirectory($this->tmp, 0755, true);
});

afterEach(function () {
    File::deleteDirectory($this->tmp);
});

function manifest(string $dir, string $slug, array $extra = []): void
{
    File::makeDirectory($dir.'/'.$slug, 0755, true);
    File::put($dir.'/'.$slug.'/manifest.json', json_encode([
        'slug' => $slug,
        'name' => ucfirst($slug),
        'is_premium' => false,
        'default_palette' => ['primary' => '#000'],
        'default_fonts' => ['display' => 'Inter'],
        'default_section_variants' => ['cover' => 'arch'],
        'layout' => ['default' => [], 'pages' => []],
        ...$extra,
    ]));
}

it('discovers all themes from manifest.json files', function () {
    manifest($this->tmp, 'alpha');
    manifest($this->tmp, 'beta');

    $registry = new ThemeRegistry($this->tmp);
    $all = $registry->all();

    expect($all)->toHaveCount(2)
        ->and($all['alpha'])->toBeInstanceOf(Theme::class)
        ->and($all['alpha']->name)->toBe('Alpha')
        ->and($all['beta']->slug)->toBe('beta');
});

it('returns null for unknown slug', function () {
    manifest($this->tmp, 'alpha');
    $registry = new ThemeRegistry($this->tmp);

    expect($registry->find('does-not-exist'))->toBeNull();
});

it('resolves variant from default mapping with explicit override', function () {
    manifest($this->tmp, 'alpha', [
        'default_section_variants' => ['cover' => 'arch', 'gift' => 'cashless-modal'],
    ]);
    $theme = (new ThemeRegistry($this->tmp))->find('alpha');

    expect($theme->variantFor('cover'))->toBe('arch')
        ->and($theme->variantFor('gift'))->toBe('cashless-modal')
        ->and($theme->variantFor('rsvp'))->toBe('default')
        ->and($theme->variantFor('cover', 'photo-blur'))->toBe('photo-blur');
});

it('exposes default layout slots and background', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'background' => ['file' => 'bg.webp', 'opacity' => 0.4, 'fit' => 'cover'],
                'slots' => [
                    'edge-top' => ['file' => 'florals-top.webp', 'anim_in' => 'fade-down'],
                    'middle' => ['file' => 'ornament.svg'],
                ],
            ],
        ],
    ]);
    $theme = (new ThemeRegistry($this->tmp))->find('alpha');

    expect($theme->background())->toMatchArray(['file' => 'bg.webp', 'opacity' => 0.4, 'fit' => 'cover'])
        ->and($theme->slots())->toHaveCount(2)
        ->and($theme->slots()['edge-top']['anim_in'])->toBe('fade-down')
        ->and($theme->lottie())->toBeNull();
});

it('returns null background and empty slots when not configured', function () {
    manifest($this->tmp, 'alpha');
    $theme = (new ThemeRegistry($this->tmp))->find('alpha');

    expect($theme->background())->toBeNull()
        ->and($theme->slots())->toBe([])
        ->and($theme->lottie())->toBeNull();
});

it('merges per-page overrides with default layout', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'background' => ['file' => 'default-bg.webp'],
                'slots' => [
                    'edge-top' => ['file' => 'default-top.webp'],
                    'edge-bottom' => ['file' => 'default-bottom.webp'],
                ],
            ],
            'pages' => [
                'cover' => [
                    'slots' => [
                        'middle' => ['file' => 'cover-middle.svg'],
                        'edge-top' => ['file' => 'cover-top.webp'],
                    ],
                ],
            ],
        ],
    ]);
    $theme = (new ThemeRegistry($this->tmp))->find('alpha');

    // Default page: 2 slots
    $defaultSlots = $theme->slots();
    expect($defaultSlots)->toHaveCount(2)
        ->and($defaultSlots['edge-top']['file'])->toBe('default-top.webp');

    // Cover page: 3 slots (default merged + override)
    $coverSlots = $theme->slots('cover');
    expect($coverSlots)->toHaveCount(3)
        ->and($coverSlots['edge-top']['file'])->toBe('cover-top.webp')
        ->and($coverSlots['edge-bottom']['file'])->toBe('default-bottom.webp')
        ->and($coverSlots['middle']['file'])->toBe('cover-middle.svg');
});

it('rejects unknown slot name in manifest', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'slots' => ['unknown-slot' => ['file' => 'x.webp']],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, 'unknown-slot');
});

it('rejects invalid anim_in preset', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'slots' => ['middle' => ['file' => 'x.webp', 'anim_in' => 'space-warp']],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, "anim_in 'space-warp' invalid");
});

it('rejects invalid background fit', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'background' => ['file' => 'bg.webp', 'fit' => 'stretch'],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, "fit 'stretch' invalid");
});

it('rejects disallowed file extension on slot', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'slots' => ['middle' => ['file' => 'evil.exe']],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, 'disallowed extension');
});

it('accepts duration_ms and delay_ms within range', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'slots' => [
                    'middle' => [
                        'file' => 'x.svg',
                        'anim_in' => 'fade-in',
                        'duration_ms' => 1200,
                        'delay_ms' => 500,
                    ],
                ],
            ],
        ],
    ]);

    $theme = (new ThemeRegistry($this->tmp))->find('alpha');

    expect($theme->slots()['middle']['duration_ms'])->toBe(1200)
        ->and($theme->slots()['middle']['delay_ms'])->toBe(500);
});

it('rejects duration_ms below minimum', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'slots' => ['middle' => ['file' => 'x.svg', 'duration_ms' => 50]],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, 'duration_ms must be');
});

it('rejects duration_ms above maximum', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'slots' => ['middle' => ['file' => 'x.svg', 'duration_ms' => 5000]],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, 'duration_ms must be');
});

it('rejects delay_ms above maximum', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'slots' => ['middle' => ['file' => 'x.svg', 'delay_ms' => 3000]],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, 'delay_ms must be');
});

it('rejects non-json lottie file', function () {
    manifest($this->tmp, 'alpha', [
        'layout' => [
            'default' => [
                'lottie' => ['file' => 'anim.webp'],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, 'lottie.file must be .json');
});

it('skips directories without manifest.json', function () {
    File::makeDirectory($this->tmp.'/empty', 0755, true);
    manifest($this->tmp, 'with-manifest');

    $registry = new ThemeRegistry($this->tmp);
    expect($registry->all())->toHaveCount(1)
        ->and($registry->find('empty'))->toBeNull();
});
