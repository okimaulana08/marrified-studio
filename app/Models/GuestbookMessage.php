<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GuestbookMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invitation_id
 * @property int|null $guest_id
 * @property string $name
 * @property string $message
 * @property bool $is_visible
 */
final class GuestbookMessage extends Model
{
    /** @use HasFactory<GuestbookMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'guest_id',
        'name',
        'message',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
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
