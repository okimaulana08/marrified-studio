<?php

declare(strict_types=1);

use App\Livewire\Admin\Forms\LayoutForm;

it('initializes with empty default slots and empty per-page slots', function () {
    $form = new LayoutForm;

    expect($form->slots)->toHaveCount(11)
        ->and($form->slots['middle']['file'])->toBe('')
        ->and($form->pages)->toHaveKey('cover')
        ->and($form->pages)->toHaveKey('guestbook')
        ->and($form->pages['cover']['slots'])->toHaveCount(11)
        ->and($form->pages['cover']['slots']['middle']['file'])->toBe('')
        ->and($form->activePage)->toBe('');
});

it('fills both default and per-page slots from manifest', function () {
    $form = new LayoutForm;

    $form->fillFromManifest([
        'default' => [
            'background' => ['file' => 'bg.webp', 'opacity' => 0.4, 'fit' => 'contain'],
            'slots' => [
                'edge-top' => ['file' => 'top.webp', 'anim_in' => 'fade-down'],
            ],
        ],
        'pages' => [
            'cover' => [
                'slots' => [
                    'middle' => ['file' => 'rings.svg', 'anim_in' => 'scale-in', 'duration_ms' => 1200],
                ],
            ],
            'couple' => [
                'slots' => [
                    'corner-tl' => ['file' => 'heart.svg'],
                ],
            ],
        ],
    ]);

    expect($form->bgFile)->toBe('bg.webp')
        ->and($form->bgFit)->toBe('contain')
        ->and($form->slots['edge-top']['file'])->toBe('top.webp')
        ->and($form->slots['edge-top']['anim_in'])->toBe('fade-down')
        ->and($form->pages['cover']['slots']['middle']['file'])->toBe('rings.svg')
        ->and($form->pages['cover']['slots']['middle']['duration_ms'])->toBe(1200)
        ->and($form->pages['couple']['slots']['corner-tl']['file'])->toBe('heart.svg');
});

it('serializes default and per-page slots into manifest patch', function () {
    $form = new LayoutForm;

    $form->bgFile = 'sky.webp';
    $form->bgOpacity = 0.5;
    $form->bgFit = 'cover';
    $form->slots['edge-top'] = ['file' => 'top.webp', 'anim_in' => 'fade-down', 'duration_ms' => 0, 'delay_ms' => 0];
    $form->pages['cover']['slots']['middle'] = ['file' => 'rings.svg', 'anim_in' => 'scale-in', 'duration_ms' => 1200, 'delay_ms' => 200];

    $patch = $form->toManifestPatch();

    expect($patch['layout']['default']['background']['file'])->toBe('sky.webp')
        ->and($patch['layout']['default']['slots']['edge-top']['file'])->toBe('top.webp')
        ->and($patch['layout']['default']['slots']['edge-top']['anim_in'])->toBe('fade-down')
        ->and($patch['layout']['default']['slots']['edge-top'])->not->toHaveKey('duration_ms')
        ->and($patch['layout']['pages']['cover']['slots']['middle']['file'])->toBe('rings.svg')
        ->and($patch['layout']['pages']['cover']['slots']['middle']['duration_ms'])->toBe(1200)
        ->and($patch['layout']['pages']['cover']['slots']['middle']['delay_ms'])->toBe(200);
});

it('omits per-page entries that have no filled slots', function () {
    $form = new LayoutForm;

    $form->slots['edge-top'] = ['file' => 'top.webp', 'anim_in' => '', 'duration_ms' => 0, 'delay_ms' => 0];
    // pages remain empty after init (no files in any per-page slot)

    $patch = $form->toManifestPatch();

    // pages should be empty object (not array) so JSON encodes to {} not []
    expect($patch['layout']['pages'])->toBeObject()
        ->and((array) $patch['layout']['pages'])->toBe([]);
});

it('round-trips a manifest through fill and toManifestPatch', function () {
    $original = [
        'default' => [
            'background' => ['file' => 'bg.webp', 'opacity' => 0.5, 'fit' => 'cover'],
            'slots' => [
                'edge-top' => ['file' => 'top.webp', 'anim_in' => 'fade-down'],
            ],
            'lottie' => ['file' => 'sparkle.json', 'placement' => 'top', 'loop' => true],
        ],
        'pages' => [
            'cover' => [
                'slots' => [
                    'middle' => ['file' => 'arch.webp', 'anim_in' => 'scale-in', 'duration_ms' => 1500],
                ],
            ],
        ],
    ];

    $form = new LayoutForm;
    $form->fillFromManifest($original);
    $patch = $form->toManifestPatch();

    expect($patch['layout']['default']['background']['file'])->toBe('bg.webp')
        ->and($patch['layout']['default']['slots']['edge-top']['anim_in'])->toBe('fade-down')
        ->and($patch['layout']['default']['lottie']['placement'])->toBe('top')
        ->and($patch['layout']['pages']['cover']['slots']['middle']['file'])->toBe('arch.webp')
        ->and($patch['layout']['pages']['cover']['slots']['middle']['duration_ms'])->toBe(1500);
});
