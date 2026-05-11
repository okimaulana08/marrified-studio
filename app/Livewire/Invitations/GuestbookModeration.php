<?php

declare(strict_types=1);

namespace App\Livewire\Invitations;

use App\Models\GuestbookMessage;
use App\Models\Invitation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Moderation panel for guestbook messages. Couple/admin can hide spam or
 * inappropriate entries from the public render without losing the data
 * (toggle is_visible flag) — or delete permanently.
 */
final class GuestbookModeration extends Component
{
    use WithPagination;

    public int $invitationId;

    public bool $isAdmin = false;

    public string $search = '';

    /** all | visible | hidden */
    public string $filter = 'all';

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    public bool $showDeleteModal = false;

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    public function mount(int $invitationId, bool $isAdmin = false): void
    {
        $this->invitationId = $invitationId;
        $this->isAdmin = $isAdmin;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function toggleVisibility(int $id): void
    {
        $invitation = $this->resolveInvitation();
        $msg = GuestbookMessage::query()
            ->where('invitation_id', $invitation->id)
            ->findOrFail($id);

        $msg->update(['is_visible' => ! $msg->is_visible]);

        $this->flash(
            $msg->is_visible ? "Ucapan dari '{$msg->name}' di-tampilkan." : "Ucapan dari '{$msg->name}' di-sembunyikan.",
            'success'
        );
    }

    public function confirmDelete(int $id): void
    {
        $invitation = $this->resolveInvitation();
        $msg = GuestbookMessage::query()
            ->where('invitation_id', $invitation->id)
            ->findOrFail($id);

        $this->deleteTargetId = $msg->id;
        $this->deleteTargetName = $msg->name;
        $this->showDeleteModal = true;
    }

    public function deleteMessage(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $invitation = $this->resolveInvitation();
        GuestbookMessage::query()
            ->where('invitation_id', $invitation->id)
            ->where('id', $this->deleteTargetId)
            ->delete();

        $name = $this->deleteTargetName;
        $this->closeDeleteModal();
        $this->flash("Ucapan dari '{$name}' dihapus.", 'success');
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
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
    }

    public function render(): View
    {
        $term = trim($this->search);
        $query = GuestbookMessage::query()
            ->where('invitation_id', $this->invitationId)
            ->when($this->filter === 'visible', fn ($q) => $q->where('is_visible', true))
            ->when($this->filter === 'hidden', fn ($q) => $q->where('is_visible', false))
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.strtolower($term).'%';
                $q->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(message) LIKE ?', [$like]);
                });
            })
            ->latest();

        $base = GuestbookMessage::query()->where('invitation_id', $this->invitationId);
        $counts = [
            'all' => (clone $base)->count(),
            'visible' => (clone $base)->where('is_visible', true)->count(),
            'hidden' => (clone $base)->where('is_visible', false)->count(),
        ];

        return view('livewire.invitations.partials.tab-guestbook-moderation', [
            'messages' => $query->paginate(20),
            'counts' => $counts,
        ]);
    }
}
