<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CoupleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $invitation_id
 * @property string $bride_name
 * @property string|null $bride_nickname
 * @property string|null $bride_parents
 * @property string|null $bride_instagram
 * @property string|null $bride_photo_path
 * @property string $groom_name
 * @property string|null $groom_nickname
 * @property string|null $groom_parents
 * @property string|null $groom_instagram
 * @property string|null $groom_photo_path
 */
final class Couple extends Model
{
    /** @use HasFactory<CoupleFactory> */
    use HasFactory;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['bride_name', 'groom_name', 'bride_photo_path', 'groom_photo_path'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('couple');
    }

    protected $fillable = [
        'invitation_id',
        'bride_name', 'bride_nickname', 'bride_parents', 'bride_instagram', 'bride_photo_path',
        'groom_name', 'groom_nickname', 'groom_parents', 'groom_instagram', 'groom_photo_path',
    ];

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
