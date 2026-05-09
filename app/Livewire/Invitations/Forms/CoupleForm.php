<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Models\Couple;
use App\Models\Invitation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Form;

/**
 * 1:1 record per invitation. Photos upload through the parent component
 * (only Livewire components can use WithFileUploads, not Form objects),
 * but `persist()` still owns the disk write so all couple-state mutations
 * stay in one place.
 */
final class CoupleForm extends Form
{
    private const PHOTO_DISK = 'invitation_media';

    #[Validate('required|string|max:120')]
    public string $brideName = '';

    #[Validate('nullable|string|max:60')]
    public string $brideNickname = '';

    #[Validate('nullable|string|max:200')]
    public string $brideParents = '';

    #[Validate('nullable|string|max:60')]
    public string $brideInstagram = '';

    public ?string $bridePhotoPath = null;

    #[Validate('required|string|max:120')]
    public string $groomName = '';

    #[Validate('nullable|string|max:60')]
    public string $groomNickname = '';

    #[Validate('nullable|string|max:200')]
    public string $groomParents = '';

    #[Validate('nullable|string|max:60')]
    public string $groomInstagram = '';

    public ?string $groomPhotoPath = null;

    public function fillFromModel(?Couple $couple): void
    {
        if ($couple === null) {
            return;
        }

        $this->brideName = $couple->bride_name;
        $this->brideNickname = (string) $couple->bride_nickname;
        $this->brideParents = (string) $couple->bride_parents;
        $this->brideInstagram = (string) $couple->bride_instagram;
        $this->bridePhotoPath = $couple->bride_photo_path;

        $this->groomName = $couple->groom_name;
        $this->groomNickname = (string) $couple->groom_nickname;
        $this->groomParents = (string) $couple->groom_parents;
        $this->groomInstagram = (string) $couple->groom_instagram;
        $this->groomPhotoPath = $couple->groom_photo_path;
    }

    public function persist(Invitation $invitation, ?UploadedFile $bridePhoto, ?UploadedFile $groomPhoto): Couple
    {
        $couple = Couple::query()->firstOrNew(['invitation_id' => $invitation->id]);

        $couple->fill([
            'bride_name' => $this->brideName,
            'bride_nickname' => $this->brideNickname !== '' ? $this->brideNickname : null,
            'bride_parents' => $this->brideParents !== '' ? $this->brideParents : null,
            'bride_instagram' => $this->brideInstagram !== '' ? $this->brideInstagram : null,
            'groom_name' => $this->groomName,
            'groom_nickname' => $this->groomNickname !== '' ? $this->groomNickname : null,
            'groom_parents' => $this->groomParents !== '' ? $this->groomParents : null,
            'groom_instagram' => $this->groomInstagram !== '' ? $this->groomInstagram : null,
        ]);

        if ($bridePhoto !== null) {
            $couple->bride_photo_path = $this->storePhoto($invitation->id, 'bride', $bridePhoto, $couple->bride_photo_path);
            $this->bridePhotoPath = $couple->bride_photo_path;
        }

        if ($groomPhoto !== null) {
            $couple->groom_photo_path = $this->storePhoto($invitation->id, 'groom', $groomPhoto, $couple->groom_photo_path);
            $this->groomPhotoPath = $couple->groom_photo_path;
        }

        $couple->save();

        return $couple;
    }

    /**
     * Store a photo with a deterministic filename ({role}.{ext}). Replaces any
     * previous photo for the same role even if the extension changed.
     */
    private function storePhoto(int $invitationId, string $role, UploadedFile $file, ?string $previousPath): string
    {
        $disk = Storage::disk(self::PHOTO_DISK);
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $dir = "{$invitationId}/couple";
        $filename = "{$role}.{$ext}";
        $path = "{$dir}/{$filename}";

        if ($previousPath !== null && $previousPath !== $path && $disk->exists($previousPath)) {
            $disk->delete($previousPath);
        }

        $file->storeAs($dir, $filename, self::PHOTO_DISK);

        return $path;
    }
}
