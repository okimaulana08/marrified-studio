<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $slug
 * @property string|null $religion_type
 * @property array<string, mixed>|null $religious_text
 * @property string $theme_slug
 * @property array<string, mixed>|null $customizations
 * @property-read Couple|null $couple
 * @property-read Collection<int, Event> $events
 * @property-read Collection<int, Guest> $guests
 * @property-read Collection<int, Section> $sections
 * @property-read Collection<int, GuestbookMessage> $guestbookMessages
 * @property-read Collection<int, Rsvp> $rsvps
 * @property-read Collection<int, GiftAccount> $giftAccounts
 */
final class Invitation extends Model
{
    /** @use HasFactory<InvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'religion_type',
        'religious_text',
        'theme_slug',
        'customizations',
    ];

    protected function casts(): array
    {
        return [
            'religious_text' => 'array',
            'customizations' => 'array',
        ];
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
