<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Section;
use App\Models\User;
use App\Services\Invitations\InvitationWriter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('invitation_media');
    $this->actingAs(User::factory()->admin()->create());
    $this->invitation = (new InvitationWriter)->create([
        'slug' => 'story-test',
        'theme_slug' => 'watercolor-lush',
        'religion_type' => 'islam',
    ]);
});

it('seeds a story section row when invitation is created', function () {
    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'story')
        ->first();

    expect($section)->not->toBeNull()
        ->and($section->variant)->toBe('timeline-vertical')
        ->and($section->enabled)->toBeTrue();
});

it('starts with empty stories rows', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->assertSet('stories.rows', []);
});

it('adds, persists, and saves story entries', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addStoryRow')
        ->set('stories.rows.0.year', '2018')
        ->set('stories.rows.0.title', 'Pertama bertemu')
        ->set('stories.rows.0.description', 'Di kampus, saat orientasi mahasiswa baru.')
        ->call('saveStories')
        ->assertSet('flashType', 'success');

    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'story')
        ->firstOrFail();

    $entries = $section->content['entries'] ?? [];
    expect($entries)->toHaveCount(1)
        ->and($entries[0]['year'])->toBe('2018')
        ->and($entries[0]['title'])->toBe('Pertama bertemu')
        ->and($entries[0]['photo_path'])->toBeNull();
});

it('rejects empty year and title', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addStoryRow')
        ->set('stories.rows.0.year', '')
        ->set('stories.rows.0.title', '')
        ->call('saveStories')
        ->assertHasErrors(['stories.rows.0.year', 'stories.rows.0.title']);
});

it('uploads per-row photo and stores in story directory', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addStoryRow')
        ->set('stories.rows.0.year', '2020')
        ->set('stories.rows.0.title', 'Pacaran')
        ->set('storyPhotos.0', UploadedFile::fake()->image('first.jpg', 800, 600))
        ->call('saveStories')
        ->assertSet('flashType', 'success');

    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'story')
        ->firstOrFail();

    $path = $section->content['entries'][0]['photo_path'];
    expect($path)->toStartWith("{$this->invitation->id}/story/");
    Storage::disk('invitation_media')->assertExists($path);
});

it('removes story row deletes any associated photo', function () {
    $component = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addStoryRow')
        ->set('stories.rows.0.year', '2020')
        ->set('stories.rows.0.title', 'Test')
        ->set('storyPhotos.0', UploadedFile::fake()->image('a.jpg'))
        ->call('saveStories');

    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'story')
        ->firstOrFail();
    $path = $section->content['entries'][0]['photo_path'];
    Storage::disk('invitation_media')->assertExists($path);

    // Now remove the row and persist
    $component->call('removeStoryRow', 0)->call('saveStories');

    Storage::disk('invitation_media')->assertMissing($path);
    $sectionAfter = $section->fresh();
    expect($sectionAfter->content['entries'])->toBe([]);
});

it('reorders stories with moveDown and persists order', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addStoryRow')
        ->set('stories.rows.0.year', '2018')
        ->set('stories.rows.0.title', 'Awal')
        ->call('addStoryRow')
        ->set('stories.rows.1.year', '2025')
        ->set('stories.rows.1.title', 'Akhir')
        ->call('moveStoryDown', 0)
        ->call('saveStories');

    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'story')
        ->firstOrFail();

    $entries = $section->content['entries'];
    expect($entries[0]['title'])->toBe('Akhir')
        ->and($entries[1]['title'])->toBe('Awal');
});

it('removeStoryPhoto clears photo_path and disk file', function () {
    $component = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addStoryRow')
        ->set('stories.rows.0.year', '2020')
        ->set('stories.rows.0.title', 'Test')
        ->set('storyPhotos.0', UploadedFile::fake()->image('a.jpg'))
        ->call('saveStories');

    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'story')
        ->firstOrFail();
    $path = $section->content['entries'][0]['photo_path'];

    $component->call('removeStoryPhoto', 0)->call('saveStories');

    Storage::disk('invitation_media')->assertMissing($path);
    expect($section->fresh()->content['entries'][0]['photo_path'])->toBeNull();
});

it('rejects oversized story photo', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addStoryRow')
        ->set('stories.rows.0.year', '2020')
        ->set('stories.rows.0.title', 'Test')
        ->set('storyPhotos.0', UploadedFile::fake()->create('huge.jpg', 6000))
        ->call('saveStories')
        ->assertHasErrors(['storyPhotos.0']);
});
