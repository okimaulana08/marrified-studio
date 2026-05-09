<?php

declare(strict_types=1);

use App\Services\Themes\ThemeValidator;
use App\Services\Themes\VariantScanner;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tmp = storage_path('framework/testing/validator-'.uniqid());
    File::makeDirectory($this->tmp.'/sample-theme/assets', 0755, true);

    // Variant scanner uses the real views path; we just need a working instance.
    $this->validator = new ThemeValidator(
        new VariantScanner(resource_path('views/sections')),
        $this->tmp,
    );
});

afterEach(function () {
    File::deleteDirectory($this->tmp);
});

function writeAsset(string $tmp, string $filename, string $contents): void
{
    File::put($tmp.'/sample-theme/assets/'.$filename, $contents);
}

function lottiePatch(string $file): array
{
    return [
        'layout' => [
            'default' => [
                'lottie' => ['file' => $file, 'placement' => 'center', 'loop' => true],
            ],
        ],
    ];
}

function validLottieJson(): string
{
    return json_encode([
        'v' => '5.5.7',
        'fr' => 30,
        'ip' => 0,
        'op' => 60,
        'w' => 200,
        'h' => 200,
        'nm' => 'test',
        'ddd' => 0,
        'assets' => [],
        'layers' => [
            [
                'ddd' => 0, 'ind' => 1, 'ty' => 4, 'nm' => 'l',
                'ks' => ['o' => ['a' => 0, 'k' => 100], 'p' => ['a' => 0, 'k' => [100, 100, 0]]],
                'shapes' => [],
                'ip' => 0, 'op' => 60, 'st' => 0,
            ],
        ],
    ]);
}

it('accepts a structurally valid lottie file', function () {
    writeAsset($this->tmp, 'good.json', validLottieJson());

    expect(fn () => $this->validator->validate('sample-theme', lottiePatch('good.json')))
        ->not->toThrow(Exception::class);
});

it('rejects a lottie file that is not valid JSON', function () {
    writeAsset($this->tmp, 'broken.json', '{not really json,,,');

    expect(fn () => $this->validator->validate('sample-theme', lottiePatch('broken.json')))
        ->toThrow(RuntimeException::class, 'not valid JSON');
});

it('rejects a lottie file missing required keys', function () {
    // Missing 'layers', 'op', 'w', 'h'
    writeAsset($this->tmp, 'partial.json', json_encode(['v' => '5.5', 'fr' => 30, 'ip' => 0]));

    expect(fn () => $this->validator->validate('sample-theme', lottiePatch('partial.json')))
        ->toThrow(RuntimeException::class, 'missing required key(s)');
});

it('rejects a lottie file with empty layers array', function () {
    $data = json_decode(validLottieJson(), true);
    $data['layers'] = [];
    writeAsset($this->tmp, 'empty-layers.json', json_encode($data));

    expect(fn () => $this->validator->validate('sample-theme', lottiePatch('empty-layers.json')))
        ->toThrow(RuntimeException::class, "empty 'layers' array");
});

it('rejects a lottie reference with non-json extension even after asset exists', function () {
    writeAsset($this->tmp, 'fake.webp', validLottieJson());

    expect(fn () => $this->validator->validate('sample-theme', lottiePatch('fake.webp')))
        ->toThrow(RuntimeException::class, '.json extension');
});

it('validates lottie referenced inside per-page override too', function () {
    writeAsset($this->tmp, 'broken.json', '{garbage');

    $patch = [
        'layout' => [
            'pages' => [
                'cover' => [
                    'lottie' => ['file' => 'broken.json', 'placement' => 'center'],
                ],
            ],
        ],
    ];

    expect(fn () => $this->validator->validate('sample-theme', $patch))
        ->toThrow(RuntimeException::class, 'not valid JSON');
});
