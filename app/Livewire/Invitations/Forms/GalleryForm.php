<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Models\Invitation;
use App\Models\Section;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Form;

/**
 * Gallery photos for an invitation. Storage is `Section.content['images']` —
 * an ordered array of paths (relative to invitation_media disk root) like
 * `{invitation_id}/gallery/{ulid}.jpg`.
 *
 * Files are written deterministically with ULID filenames so two uploads of
 * the same source filename don't collide. Removal deletes the disk file
 * immediately AND removes the path from the array; persist() flushes the
 * resulting array to the Section.content JSON column.
 */
final class GalleryForm extends Form
{
    public const PHOTO_DISK = 'invitation_media';

    public const MAX_IMAGES = 20;

    public const MAX_FILE_KB = 5120;

    /** @var list<string> ordered list of relative paths */
    public array $images = [];

    public function rules(): array
    {
        return [];
    }

    public function fillFromSection(?Section $section): void
    {
        $content = (array) ($section?->content ?? []);
        $images = (array) ($content['images'] ?? []);

        $this->images = array_values(array_filter(
            $images,
            fn ($p) => is_string($p) && $p !== '',
        ));
    }

    /**
     * Store an uploaded file on disk and append its path to the images array.
     * Returns the relative path that was added.
     */
    public function appendUploaded(Invitation $invitation, UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $dir = "{$invitation->id}/gallery";
        $filename = (string) Str::ulid().'.'.$ext;

        $file->storeAs($dir, $filename, self::PHOTO_DISK);

        $path = "{$dir}/{$filename}";
        $this->images[] = $path;

        return $path;
    }

    public function removeImage(int $index): void
    {
        if (! isset($this->images[$index])) {
            return;
        }

        $path = $this->images[$index];
        $disk = Storage::disk(self::PHOTO_DISK);
        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function moveUp(int $index): void
    {
        if ($index <= 0 || ! isset($this->images[$index])) {
            return;
        }
        [$this->images[$index - 1], $this->images[$index]] = [$this->images[$index], $this->images[$index - 1]];
    }

    public function moveDown(int $index): void
    {
        if (! isset($this->images[$index + 1])) {
            return;
        }
        [$this->images[$index], $this->images[$index + 1]] = [$this->images[$index + 1], $this->images[$index]];
    }

    /**
     * Flush current images array to the gallery Section's content JSON.
     * No-op if no gallery section row exists (shouldn't happen since the
     * 8 default sections are auto-seeded on invitation create).
     */
    public function persist(Invitation $invitation): void
    {
        $section = Section::query()
            ->where('invitation_id', $invitation->id)
            ->where('type', 'gallery')
            ->first();

        if ($section === null) {
            return;
        }

        $content = (array) ($section->content ?? []);
        $content['images'] = $this->images;
        $section->content = $content;
        $section->save();
    }
}
