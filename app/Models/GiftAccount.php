<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GiftAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invitation_id
 * @property string $type
 * @property string $bank_name
 * @property string $account_number
 * @property string $account_name
 * @property int $sort_order
 */
final class GiftAccount extends Model
{
    /** @use HasFactory<GiftAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'type',
        'bank_name',
        'account_number',
        'account_name',
        'sort_order',
    ];

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
