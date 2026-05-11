<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $invitation_id
 * @property string $type
 * @property string $name
 * @property CarbonImmutable $date
 * @property string|null $time
 * @property string $venue_name
 * @property string|null $venue_address
 * @property string|null $maps_url
 * @property int $sort_order
 */
final class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'name', 'date', 'time', 'venue_name', 'venue_address'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('event');
    }

    protected $fillable = [
        'invitation_id',
        'type',
        'name',
        'date',
        'time',
        'venue_name',
        'venue_address',
        'maps_url',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
        ];
    }

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
