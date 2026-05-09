<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\InvitationObserver;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $slug
 * @property string|null $religion_type
 * @property array<string, mixed>|null $religious_text
 * @property string $theme_slug
 * @property int|null $music_track_id
 * @property array<string, mixed>|null $customizations
 * @property-read User|null $user
 * @property-read MusicTrack|null $musicTrack
 * @property-read Couple|null $couple
 * @property-read Collection<int, Event> $events
 * @property-read Collection<int, Guest> $guests
 * @property-read Collection<int, Section> $sections
 * @property-read Collection<int, GuestbookMessage> $guestbookMessages
 * @property-read Collection<int, Rsvp> $rsvps
 * @property-read Collection<int, GiftAccount> $giftAccounts
 */
#[ObservedBy([InvitationObserver::class])]
final class Invitation extends Model
{
    /** @use HasFactory<InvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'religion_type',
        'religious_text',
        'theme_slug',
        'music_track_id',
        'customizations',
    ];

    protected function casts(): array
    {
        return [
            'religious_text' => 'array',
            'customizations' => 'array',
        ];
    }

    /**
     * Owning couple-user. Null when admin created the invitation but hasn't
     * issued couple credentials yet.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<MusicTrack, $this> */
    public function musicTrack(): BelongsTo
    {
        return $this->belongsTo(MusicTrack::class);
    }

    public function couple(): HasOne
    {
        return $this->hasOne(Couple::class);
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class)->orderBy('sort_order');
    }

    /** @return HasMany<Guest, $this> */
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    /** @return HasMany<Section, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    /** @return HasMany<GuestbookMessage, $this> */
    public function guestbookMessages(): HasMany
    {
        return $this->hasMany(GuestbookMessage::class);
    }

    /** @return HasMany<Rsvp, $this> */
    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    /** @return HasMany<GiftAccount, $this> */
    public function giftAccounts(): HasMany
    {
        return $this->hasMany(GiftAccount::class)->orderBy('sort_order');
    }
}
