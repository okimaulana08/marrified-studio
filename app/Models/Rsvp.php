<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RsvpFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invitation_id
 * @property int|null $guest_id
 * @property string $name
 * @property string|null $phone
 * @property string $attendance
 * @property int $party_size
 * @property string|null $note
 */
final class Rsvp extends Model
{
    /** @use HasFactory<RsvpFactory> */
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'guest_id',
        'name',
        'phone',
        'attendance',
        'party_size',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'party_size' => 'integer',
        ];
    }

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /** @return BelongsTo<Guest, $this> */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
