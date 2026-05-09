<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Invitation;
use App\Models\MusicTrack;
use App\Models\User;
use App\Services\Invitations\InvitationWriter;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    $this->invitation = (new InvitationWriter)->create([
        'slug' => 'music-test',
        'theme_slug' => 'watercolor-lush',
        'religion_type' => 'islam',
    ]);
});

it('starts with no music track linked', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->assertSet('music.musicTrackId', null);
});

it('couple picks a track and persists music_track_id', function () {
    $track = MusicTrack::factory()->create(['title' => 'Wedding March']);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('music.musicTrackId', $track->id)
        ->call('saveMusic')
        ->assertSet('flashType', 'success');

    expect($this->invitation->fresh()->music_track_id)->toBe($track->id);
});

it('rejects non-existent track id', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('music.musicTrackId', 99999)
        ->call('saveMusic')
        ->assertHasErrors(['music.musicTrackId']);
});

it('clears music selection when set to null', function () {
    $track = MusicTrack::factory()->create();
    $this->invitation->update(['music_track_id' => $track->id]);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->set('music.musicTrackId', null)
        ->call('saveMusic')
        ->assertSet('flashType', 'success');

    expect($this->invitation->fresh()->music_track_id)->toBeNull();
});

it('FK ON DELETE SET NULL keeps invitation alive when track is deleted', function () {
    $track = MusicTrack::factory()->create();
    $this->invitation->update(['music_track_id' => $track->id]);

    $track->delete();

    expect(Invitation::query()->find($this->invitation->id))->not->toBeNull()
        ->and($this->invitation->fresh()->music_track_id)->toBeNull();
});
