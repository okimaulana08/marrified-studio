<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Invitations;

use App\Models\Invitation;
use App\Services\Invitations\InvitationWriter;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Admin-only list of every invitation in the system. Search across slug +
 * couple names. Each row links to the shared editor and the credentials
 * manager (Phase 6). Delete is gated by InvitationPolicy::delete (admin only).
 */
final class InvitationList extends Component
{
    public string $search = '';

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetSlug = '';

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    public function confirmDelete(int $id): void
    {
        $invitation = Invitation::query()->findOrFail($id);
        $this->authorize('delete', $invitation);

        $this->deleteTargetId = $invitation->id;
        $this->deleteTargetSlug = $invitation->slug;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetSlug = '';
    }

    public function deleteInvitation(InvitationWriter $writer): void
    {
        $invitation = Invitation::query()->findOrFail($this->deleteTargetId);
        $this->authorize('delete', $invitation);

        $writer->delete($invitation);

        $this->closeDeleteModal();
        $this->flashMessage = "Invitation '{$invitation->slug}' dihapus.";
        $this->flashType = 'success';
    }

    public function render(): View
    {
        $rows = Invitation::query()
            ->with(['couple:id,invitation_id,bride_name,groom_name', 'user:id,email'])
            ->withCount('guests')
            ->when($this->search !== '', function ($q) {
                $term = '%'.strtolower($this->search).'%';
                $q->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(slug) LIKE ?', [$term])
                        ->orWhereHas('couple', function ($q) use ($term) {
                            $q->whereRaw('LOWER(bride_name) LIKE ?', [$term])
                                ->orWhereRaw('LOWER(groom_name) LIKE ?', [$term]);
                        });
                });
            })
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.invitations.invitation-list', ['rows' => $rows]);
    }
}
