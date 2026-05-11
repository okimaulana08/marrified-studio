<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invitation_id
 * @property string $name
 * @property string $relation
 * @property string|null $phone
 * @property string $token
 * @property int $opens_count
 * @property CarbonImmutable|null $first_opened_at
 * @property CarbonImmutable|null $last_opened_at
 */
final class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'name',
        'relation',
        'group',
        'phone',
        'token',
        'opens_count',
        'first_opened_at',
        'last_opened_at',
    ];

    protected function casts(): array
    {
        return [
            'opens_count' => 'integer',
            'first_opened_at' => 'immutable_datetime',
            'last_opened_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
