<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Music;

use App\Models\MusicTrack;
use App\Services\Music\MusicLibrary;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Admin-only library manager. Upload form on top + table list below with
 * inline audio preview + delete per row. Authorization enforced by the
 * `auth + role:admin` route group; component re-validates defensively on
 * each mutation just in case.
 */
final class MusicLibraryManager extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:mp3|max:8192')]
    public $newFile = null;

    #[Validate('required|string|max:120')]
    public string $title = '';

    #[Validate('nullable|string|max:120')]
    public string $artist = '';

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetTitle = '';

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    /**
     * NOTE: method name avoids `upload` because Livewire reserves that as a
     * magic JS action ($wire.upload(name, file, ...)) — wire:click="upload"
     * would invoke the JS file-upload bridge instead of this PHP method.
     */
    public function uploadTrack(MusicLibrary $library): void
    {
        $this->validate();

        if (! ($this->newFile instanceof TemporaryUploadedFile)) {
            $this->flash('File belum dipilih.', 'error');

            return;
        }

        $library->upload($this->newFile, [
            'title' => $this->title,
            'artist' => $this->artist !== '' ? $this->artist : null,
            'uploaded_by' => Auth::id(),
        ]);

        $this->reset(['newFile', 'title', 'artist']);
        $this->resetErrorBag();
        $this->flash('Track berhasil di-upload.', 'success');
    }

    public function confirmDelete(int $id): void
    {
        $track = MusicTrack::query()->findOrFail($id);
        $this->deleteTargetId = $track->id;
        $this->deleteTargetTitle = $track->title;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetTitle = '';
    }

    public function deleteTrack(MusicLibrary $library): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $track = MusicTrack::query()->findOrFail($this->deleteTargetId);
        $title = $track->title;
        $library->delete($track);

        $this->closeDeleteModal();
        $this->flash("Track '{$title}' dihapus.", 'success');
    }

    private function flash(string $message, string $type): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function render(): View
    {
        $tracks = MusicTrack::query()
            ->with('uploader:id,email')
            ->withCount('invitations')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.music.library-manager', ['tracks' => $tracks]);
    }
}
