<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invitation_id
 * @property string $type
 * @property string $variant
 * @property int $sort_order
 * @property bool $enabled
 * @property array<string, mixed>|null $content
 * @property array<string, mixed>|null $decoration_overrides
 */
final class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'type',
        'variant',
        'sort_order',
        'enabled',
        'content',
        'decoration_overrides',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'content' => 'array',
            'decoration_overrides' => 'array',
        ];
    }

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
