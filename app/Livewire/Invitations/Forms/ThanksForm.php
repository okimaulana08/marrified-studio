<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Models\Invitation;
use App\Models\Section;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Form;

/**
 * Final "Thank You" page content. Stored as JSON in Section.content
 * (Section type='thanks'):
 *   { title, message, signature, photo_path }
 *
 * Photo handled here directly (single file, deterministic name) — same
 * pattern as CoupleForm::storePhoto. Parent component owns the temp upload.
 */
final class ThanksForm extends Form
{
    private const PHOTO_DISK = 'invitation_media';

    #[Validate('nullable|string|max:80')]
    public string $title = 'Terima Kasih';

    #[Validate('nullable|string|max:600')]
    public string $message = '';

    #[Validate('nullable|string|max:120')]
    public string $signature = '';

    public ?string $photoPath = null;

    public function fillFromSection(?Section $section): void
    {
        $content = (array) ($section?->content ?? []);

        $this->title = (string) ($content['title'] ?? 'Terima Kasih');
        $this->message = (string) ($content['message'] ?? '');
        $this->signature = (string) ($content['signature'] ?? '');
        $this->photoPath = isset($content['photo_path']) && $content['photo_path'] !== ''
            ? (string) $content['photo_path']
            : null;
    }

    public function persist(Invitation $invitation, ?UploadedFile $photo): void
    {
        $section = Section::query()
            ->where('invitation_id', $invitation->id)
            ->where('type', 'thanks')
            ->first();

        if ($section === null) {
            return;
        }

        if ($photo !== null) {
            $this->photoPath = $this->storePhoto($invitation->id, $photo, $this->photoPath);
        }

        $section->content = [
            'title' => $this->title !== '' ? $this->title : 'Terima Kasih',
            'message' => $this->message,
            'signature' => $this->signature,
            'photo_path' => $this->photoPath,
        ];
        $section->save();
    }

    public function removePhoto(Invitation $invitation): void
    {
        $section = Section::query()
            ->where('invitation_id', $invitation->id)
            ->where('type', 'thanks')
            ->first();

        if ($section === null) {
            return;
        }

        if ($this->photoPath !== null && Storage::disk(self::PHOTO_DISK)->exists($this->photoPath)) {
            Storage::disk(self::PHOTO_DISK)->delete($this->photoPath);
        }

        $this->photoPath = null;

        $content = (array) ($section->content ?? []);
        $content['photo_path'] = null;
        $section->content = $content;
        $section->save();
    }

    private function storePhoto(int $invitationId, UploadedFile $file, ?string $previousPath): string
    {
        $disk = Storage::disk(self::PHOTO_DISK);
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $dir = "{$invitationId}/thanks";
        $filename = "thanks.{$ext}";
        $path = "{$dir}/{$filename}";

        if ($previousPath !== null && $previousPath !== $path && $disk->exists($previousPath)) {
            $disk->delete($previousPath);
        }

        $file->storeAs($dir, $filename, self::PHOTO_DISK);

        return $path;
    }
}
