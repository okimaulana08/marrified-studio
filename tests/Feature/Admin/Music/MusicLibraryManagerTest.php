<?php

declare(strict_types=1);

use App\Livewire\Admin\Music\MusicLibraryManager;
use App\Models\MusicTrack;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('music_assets');
    $this->actingAs(User::factory()->admin()->create());
});

it('admin can access music library page', function () {
    $this->get(route('admin.music.index'))->assertOk()->assertSee('Music Library');
});

it('couple is forbidden from music library page', function () {
    $couple = User::factory()->couple()->create();
    $this->actingAs($couple);
    $this->get(route('admin.music.index'))->assertForbidden();
});

it('uploads a track via Livewire form', function () {
    Livewire::test(MusicLibraryManager::class)
        ->set('title', 'Canon in D')
        ->set('artist', 'Pachelbel')
        ->set('newFile', UploadedFile::fake()->create('canon.mp3', 1024, 'audio/mpeg'))
        ->call('uploadTrack')
        ->assertSet('flashType', 'success')
        ->assertSet('title', '')
        ->assertSet('artist', '')
        ->assertSet('newFile', null);

    $track = MusicTrack::query()->where('title', 'Canon in D')->firstOrFail();
    expect($track->artist)->toBe('Pachelbel');
    Storage::disk('music_assets')->assertExists($track->file_path);
});

it('rejects non-mp3 file', function () {
    Livewire::test(MusicLibraryManager::class)
        ->set('title', 'Bad')
        ->set('newFile', UploadedFile::fake()->create('not-music.txt', 100, 'text/plain'))
        ->call('uploadTrack')
        ->assertHasErrors(['newFile']);
});

it('rejects missing title', function () {
    Livewire::test(MusicLibraryManager::class)
        ->set('title', '')
        ->set('newFile', UploadedFile::fake()->create('song.mp3', 500, 'audio/mpeg'))
        ->call('uploadTrack')
        ->assertHasErrors(['title']);
});

it('deletes a track via confirm modal', function () {
    $track = MusicTrack::factory()->create();
    Storage::disk('music_assets')->put($track->file_path, 'fake-mp3-bytes');

    Livewire::test(MusicLibraryManager::class)
        ->call('confirmDelete', $track->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteTargetTitle', $track->title)
        ->call('deleteTrack')
        ->assertSet('showDeleteModal', false)
        ->assertSet('flashType', 'success');

    expect(MusicTrack::query()->find($track->id))->toBeNull();
    Storage::disk('music_assets')->assertMissing($track->file_path);
});
