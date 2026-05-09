<?php

declare(strict_types=1);

use App\Services\Themes\VariantScanner;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tmp = storage_path('framework/testing/variants-'.uniqid());
    $this->scanner = new VariantScanner($this->tmp);
    File::makeDirectory($this->tmp, 0755, true);
});

afterEach(function () {
    File::deleteDirectory($this->tmp);
});

it('returns blade variant names for a section type', function () {
    File::makeDirectory($this->tmp.'/cover', 0755, true);
    File::put($this->tmp.'/cover/arch.blade.php', '');
    File::put($this->tmp.'/cover/photo.blade.php', '');

    expect($this->scanner->forType('cover'))->toBe(['arch', 'photo']);
});

it('returns sorted variant list', function () {
    File::makeDirectory($this->tmp.'/rsvp', 0755, true);
    File::put($this->tmp.'/rsvp/default.blade.php', '');
    File::put($this->tmp.'/rsvp/compact.blade.php', '');
    File::put($this->tmp.'/rsvp/full-page.blade.php', '');

    expect($this->scanner->forType('rsvp'))->toBe(['compact', 'default', 'full-page']);
});

it('returns empty array for section type with no directory', function () {
    expect($this->scanner->forType('nonexistent'))->toBe([]);
});

it('returns all section types', function () {
    foreach (['cover', 'quotes', 'couple', 'event'] as $type) {
        File::makeDirectory($this->tmp.'/'.$type, 0755, true);
        File::put($this->tmp.'/'.$type.'/default.blade.php', '');
    }

    $all = $this->scanner->all();

    expect($all)->toHaveKey('cover')
        ->and($all)->toHaveKey('quotes')
        ->and($all['cover'])->toBe(['default'])
        ->and($all['gallery'])->toBe([]); // no directory created
});

it('ignores non-blade files', function () {
    File::makeDirectory($this->tmp.'/gift', 0755, true);
    File::put($this->tmp.'/gift/cashless-modal.blade.php', '');
    File::put($this->tmp.'/gift/README.md', '');
    File::put($this->tmp.'/gift/.gitkeep', '');

    expect($this->scanner->forType('gift'))->toBe(['cashless-modal']);
});
