<?php

declare(strict_types=1);

use App\Livewire\Admin\AssetLibrary;
use App\Services\Themes\AssetUsageAnalyzer;
use App\Services\Themes\ThemeRegistry;
use App\Services\Themes\VariantScanner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

beforeEach(function () {
    $this->tmp = storage_path('framework/testing/upload-'.uniqid());
    File::makeDirectory($this->tmp.'/sample-theme/assets', 0755, true);

    // Swap the theme_assets disk root to the temp dir so the test
    // doesn't pollute resources/themes.
    Config::set('filesystems.disks.theme_assets', [
        'driver' => 'local',
        'root' => $this->tmp,
        'throw' => false,
    ]);

    // Swap singletons that the Livewire component pulls from container.
    app()->singleton(ThemeRegistry::class, fn () => new ThemeRegistry($this->tmp));
    app()->singleton(AssetUsageAnalyzer::class, fn () => new AssetUsageAnalyzer($this->tmp));
    app()->singleton(VariantScanner::class, fn () => new VariantScanner(resource_path('views/sections')));

    // Stub themes:publish-assets so it doesn't try to publish to real public/.
    Artisan::command('themes:publish-assets {slug?}', fn () => 0)->describe('stub');
});

afterEach(function () {
    File::deleteDirectory($this->tmp);
});

it('uploads a webp via Livewire storeAs without UploadedFile::move()', function () {
    Livewire::test(AssetLibrary::class, ['slug' => 'sample-theme'])
        ->set('uploadedFiles', [UploadedFile::fake()->image('test.webp')])
        ->call('uploadFiles')
        ->assertHasNoErrors();

    expect(File::exists($this->tmp.'/sample-theme/assets/test.webp'))->toBeTrue();
});

it('renames on filename collision with -2, -3 suffixes', function () {
    File::put($this->tmp.'/sample-theme/assets/photo.webp', 'existing');

    Livewire::test(AssetLibrary::class, ['slug' => 'sample-theme'])
        ->set('uploadedFiles', [UploadedFile::fake()->image('photo.webp')])
        ->call('uploadFiles')
        ->assertHasNoErrors();

    expect(File::exists($this->tmp.'/sample-theme/assets/photo.webp'))->toBeTrue() // original preserved
        ->and(File::exists($this->tmp.'/sample-theme/assets/photo-2.webp'))->toBeTrue();
});

it('sanitizes weird filenames via Str::slug', function () {
    Livewire::test(AssetLibrary::class, ['slug' => 'sample-theme'])
        ->set('uploadedFiles', [UploadedFile::fake()->image('My Wedding Photo!.webp')])
        ->call('uploadFiles')
        ->assertHasNoErrors();

    expect(File::exists($this->tmp.'/sample-theme/assets/my-wedding-photo.webp'))->toBeTrue();
});

it('skips files with disallowed extension', function () {
    Livewire::test(AssetLibrary::class, ['slug' => 'sample-theme'])
        ->set('uploadedFiles', [UploadedFile::fake()->create('evil.exe', 100)])
        ->call('uploadFiles')
        ->assertHasNoErrors();

    expect(File::exists($this->tmp.'/sample-theme/assets/evil.exe'))->toBeFalse();
});
