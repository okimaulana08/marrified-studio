<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'variant', 'enabled', 'sort_order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('section');
    }

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

    /**
     * Resolve the per-section background override, if any. Returns array
     * matching the theme bg shape ({file_url, opacity, fit}) so it can be
     * used as a drop-in replacement for $theme->background($page) by the
     * layout-slots component. Returns null when this section uses the
     * theme default.
     *
     * @return array{file_url: string, opacity: float, fit: string}|null
     */
    public function resolveBgOverride(Invitation $invitation): ?array
    {
        $override = (array) ($this->content['bg_override'] ?? []);
        $source = (string) ($override['source'] ?? '');
        if ($source === '' || $source === 'default') {
            return null;
        }

        $disk = Storage::disk('invitation_media');
        $fileUrl = null;

        if ($source === 'couple_bride') {
            $path = $invitation->couple?->bride_photo_path;
            if ($path && $disk->exists($path)) {
                $fileUrl = $disk->url($path);
            }
        } elseif ($source === 'couple_groom') {
            $path = $invitation->couple?->groom_photo_path;
            if ($path && $disk->exists($path)) {
                $fileUrl = $disk->url($path);
            }
        } elseif ($source === 'gallery') {
            $gallery = $invitation->sections->firstWhere('type', 'gallery');
            $images = (array) ($gallery?->content['images'] ?? []);
            $index = (int) ($override['gallery_index'] ?? 0);
            $imagePath = $images[$index] ?? null;
            if ($imagePath && $disk->exists($imagePath)) {
                $fileUrl = $disk->url($imagePath);
            }
        } elseif ($source === 'upload') {
            $path = (string) ($override['path'] ?? '');
            if ($path !== '' && $disk->exists($path)) {
                $fileUrl = $disk->url($path);
            }
        }

        if ($fileUrl === null) {
            return null;
        }

        return [
            'file_url' => $fileUrl,
            'opacity' => (float) ($override['opacity'] ?? 1.0),
            'darken' => (float) ($override['darken'] ?? 0.0),
            'fit' => (string) ($override['fit'] ?? 'cover'),
        ];
    }
}
