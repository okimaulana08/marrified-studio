<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\MusicTrack;
use App\Models\User;
use App\Services\Music\MusicLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('music_assets');
    $this->library = new MusicLibrary;
});

it('uploads file and creates DB row', function () {
    $admin = User::factory()->admin()->create();
    $file = UploadedFile::fake()->create('song.mp3', 1024, 'audio/mpeg');

    $track = $this->library->upload($file, [
        'title' => 'Wedding March',
        'artist' => 'Mendelssohn',
        'uploaded_by' => $admin->id,
    ]);

    expect($track)
        ->toBeInstanceOf(MusicTrack::class)
        ->title->toBe('Wedding March')
        ->artist->toBe('Mendelssohn')
        ->uploaded_by->toBe($admin->id)
        ->file_path->toStartWith('tracks/')
        ->file_path->toEndWith('.mp3');

    Storage::disk('music_assets')->assertExists($track->file_path);
});

it('stores artist as null when blank', function () {
    $file = UploadedFile::fake()->create('song.mp3', 500, 'audio/mpeg');
    $track = $this->library->upload($file, ['title' => 'Solo Title', 'artist' => '   ']);
    expect($track->artist)->toBeNull();
});

it('uses ULID-based unique filenames so concurrent uploads do not collide', function () {
    $a = $this->library->upload(UploadedFile::fake()->create('a.mp3', 100, 'audio/mpeg'), ['title' => 'A']);
    $b = $this->library->upload(UploadedFile::fake()->create('b.mp3', 100, 'audio/mpeg'), ['title' => 'B']);

    expect($a->file_path)->not->toBe($b->file_path);
    Storage::disk('music_assets')->assertExists($a->file_path);
    Storage::disk('music_assets')->assertExists($b->file_path);
});

it('deletes file from disk and DB row', function () {
    $file = UploadedFile::fake()->create('song.mp3', 500, 'audio/mpeg');
    $track = $this->library->upload($file, ['title' => 'Remove Me']);
    $path = $track->file_path;

    Storage::disk('music_assets')->assertExists($path);

    $this->library->delete($track);

    Storage::disk('music_assets')->assertMissing($path);
    expect(MusicTrack::query()->find($track->id))->toBeNull();
});

it('cascade nulls invitation music_track_id when track is deleted', function () {
    $file = UploadedFile::fake()->create('song.mp3', 500, 'audio/mpeg');
    $track = $this->library->upload($file, ['title' => 'Linked']);

    $invitation = Invitation::factory()->create(['music_track_id' => $track->id]);
    expect($invitation->fresh()->music_track_id)->toBe($track->id);

    $this->library->delete($track);

    expect($invitation->fresh()->music_track_id)->toBeNull();
});
