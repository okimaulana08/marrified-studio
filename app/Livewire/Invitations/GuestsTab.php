<?php

declare(strict_types=1);

namespace App\Livewire\Invitations;

use App\Livewire\Invitations\Forms\GuestForm;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\Invitations\GuestCsvImporter;
use App\Support\GuestToken;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

/**
 * Child Livewire component nested in the InvitationEditor template. Owns:
 *   - inline add-guest form at top
 *   - paginated table of existing guests with search
 *   - per-row edit (modal) + delete (confirm dialog)
 *   - CSV import (preview-then-confirm modal)
 *
 * Authorization is delegated to InvitationPolicy::update — admin always allowed,
 * couple only on their own invitation. The parent passes the invitation id; we
 * resolve & re-authorize each mutation defensively.
 */
final class GuestsTab extends Component
{
    use WithFileUploads, WithPagination;

    public int $invitationId;

    public bool $isAdmin = false;

    public GuestForm $form;

    public ?int $editingGuestId = null;

    public bool $showEditModal = false;

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    public string $search = '';

    /** @var TemporaryUploadedFile|null */
    #[Validate('nullable|file|max:512|mimes:csv,txt')]
    public $csvFile = null;

    /** @var list<array{name: string, relation: string, phone: string, errors: list<string>}> */
    public array $csvPreview = [];

    public bool $showCsvModal = false;

    public bool $csvParsed = false;

    public ?string $csvError = null;

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function addGuest(): void
    {
        $this->form->validate();
        $invitation = $this->resolveInvitation();

        Guest::query()->create([
            'invitation_id' => $invitation->id,
            ...$this->form->toAttributes(),
            'token' => GuestToken::ensureUnique(),
        ]);

        $this->form->blank();
        $this->flash('Tamu ditambahkan.', 'success');
    }

    public function startEdit(int $guestId): void
    {
        $invitation = $this->resolveInvitation();
        $guest = Guest::query()
            ->where('invitation_id', $invitation->id)
            ->findOrFail($guestId);

        $this->editingGuestId = $guest->id;
        $this->form->fillFromGuest($guest);
        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        if ($this->editingGuestId === null) {
            return;
        }

        $invitation = $this->resolveInvitation();
        $this->form->validate();

        $guest = Guest::query()
            ->where('invitation_id', $invitation->id)
            ->findOrFail($this->editingGuestId);

        $guest->update($this->form->toAttributes());

        $this->closeEditModal();
        $this->flash("Tamu '{$guest->name}' diperbarui.", 'success');
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingGuestId = null;
        $this->form->blank();
    }

    public function confirmDelete(int $guestId): void
    {
        $invitation = $this->resolveInvitation();
        $guest = Guest::query()
            ->where('invitation_id', $invitation->id)
            ->findOrFail($guestId);

        $this->deleteTargetId = $guest->id;
        $this->deleteTargetName = $guest->name;
        $this->showDeleteModal = true;
    }

    public function deleteGuest(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $invitation = $this->resolveInvitation();
        Guest::query()
            ->where('invitation_id', $invitation->id)
            ->where('id', $this->deleteTargetId)
            ->delete();

        $name = $this->deleteTargetName;
        $this->closeDeleteModal();
        $this->flash("Tamu '{$name}' dihapus.", 'success');
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
    }

    public function openCsvModal(): void
    {
        $this->showCsvModal = true;
        $this->csvFile = null;
        $this->csvPreview = [];
        $this->csvParsed = false;
        $this->csvError = null;
    }

    public function closeCsvModal(): void
    {
        $this->showCsvModal = false;
        $this->csvFile = null;
        $this->csvPreview = [];
        $this->csvParsed = false;
        $this->csvError = null;
    }

    public function previewCsv(): void
    {
        $this->validate(['csvFile' => 'required|file|max:512|mimes:csv,txt']);
        $this->resolveInvitation();

        try {
            $this->csvPreview = app(GuestCsvImporter::class)->parse($this->csvFile);
            $this->csvParsed = true;
            $this->csvError = null;
        } catch (RuntimeException $e) {
            $this->csvPreview = [];
            $this->csvParsed = false;
            $this->csvError = $e->getMessage();
        }
    }

    public function confirmImport(): void
    {
        $invitation = $this->resolveInvitation();

        if (! $this->csvParsed || $this->csvPreview === []) {
            return;
        }

        $count = app(GuestCsvImporter::class)->import($invitation, $this->csvPreview);

        $this->closeCsvModal();
        $this->flash("{$count} tamu di-import.", 'success');
    }

    private function resolveInvitation(): Invitation
    {
        $invitation = Invitation::query()->findOrFail($this->invitationId);
        Gate::authorize('update', $invitation);

        return $invitation;
    }

    private function flash(string $message, string $type): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
        $this->dispatch('invitation-saved');
    }

    public function render(): View
    {
        $term = trim($this->search);
        $query = Guest::query()
            ->where('invitation_id', $this->invitationId)
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.strtolower($term).'%';
                $q->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(relation) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(phone) LIKE ?', [$like]);
                });
            })
            ->orderBy('name');

        $invitation = Invitation::query()->find($this->invitationId);
        $invitationSlug = $invitation?->slug ?? '';

        return view('livewire.invitations.guests-tab', [
            'guests' => $query->paginate(50),
            'invitationSlug' => $invitationSlug,
        ]);
    }
}
