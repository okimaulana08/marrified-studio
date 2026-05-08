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
        'decorations' => [],
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

it('exposes per-section decoration as nested array', function () {
    manifest($this->tmp, 'alpha', [
        'decorations' => [
            'sections' => [
                'cover' => [
                    'tossed' => ['file' => 'flowers.webp', 'slot' => 'top'],
                ],
            ],
        ],
    ]);
    $theme = (new ThemeRegistry($this->tmp))->find('alpha');

    expect($theme->decorationFor('cover'))->toMatchArray([
        'tossed' => ['file' => 'flowers.webp', 'slot' => 'top'],
    ])
        ->and($theme->decorationFor('rsvp'))->toBe([]);
});

it('rejects invalid decoration slot in manifest', function () {
    manifest($this->tmp, 'alpha', [
        'decorations' => [
            'sections' => [
                'cover' => [
                    'tossed' => ['file' => 'x.webp', 'slot' => 'INVALID'],
                ],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, "slot 'INVALID' is invalid");
});

it('rejects disallowed file extension', function () {
    manifest($this->tmp, 'alpha', [
        'decorations' => [
            'sections' => [
                'cover' => [
                    'tossed' => ['file' => 'evil.exe', 'slot' => 'top'],
                ],
            ],
        ],
    ]);

    $registry = new ThemeRegistry($this->tmp);
    expect(fn () => $registry->all())->toThrow(RuntimeException::class, 'disallowed extension');
});

it('skips directories without manifest.json', function () {
    File::makeDirectory($this->tmp.'/empty', 0755, true);
    manifest($this->tmp, 'with-manifest');

    $registry = new ThemeRegistry($this->tmp);
    expect($registry->all())->toHaveCount(1)
        ->and($registry->find('empty'))->toBeNull();
});
