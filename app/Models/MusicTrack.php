<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MusicTrackFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Admin-curated music track. Couples pick one for their invitation's
 * background music via `invitations.music_track_id` FK (nullOnDelete so
 * removing a track doesn't delete the invitation).
 *
 * @property int $id
 * @property int|null $uploaded_by
 * @property string $title
 * @property string|null $artist
 * @property string $file_path
 * @property int|null $duration_seconds
 * @property-read User|null $uploader
 * @property-read Collection<int, Invitation> $invitations
 */
final class MusicTrack extends Model
{
    /** @use HasFactory<MusicTrackFactory> */
    use HasFactory;

    protected $fillable = [
        'uploaded_by',
        'title',
        'artist',
        'file_path',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return HasMany<Invitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
