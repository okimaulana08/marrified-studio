<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Models\Invitation;
use App\Models\MusicTrack;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Form;

/**
 * Single field: which music_track_id the couple picked. `null` = no music
 * (invitation renders without `<audio>` tag).
 *
 * Validation lives in rules() — `nullable|exists:music_tracks,id` so the
 * picker can be cleared and ensure the chosen track really exists.
 */
final class MusicForm extends Form
{
    public ?int $musicTrackId = null;

    public function rules(): array
    {
        return [
            'musicTrackId' => 'nullable|integer|exists:music_tracks,id',
        ];
    }

    public function fillFromModel(Invitation $invitation): void
    {
        $this->musicTrackId = $invitation->music_track_id;
    }

    public function persist(Invitation $invitation): void
    {
        $invitation->update(['music_track_id' => $this->musicTrackId]);
    }

    /**
     * Sorted list of tracks for the picker UI. Light-weight: title/artist/file.
     *
     * @return Collection<int, MusicTrack>
     */
    public function listTracks(): Collection
    {
        return MusicTrack::query()
            ->orderBy('title')
            ->get(['id', 'title', 'artist', 'file_path', 'duration_seconds']);
    }
}
