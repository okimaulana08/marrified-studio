<?php

declare(strict_types=1);

namespace App\Livewire\Invitations;

use App\Models\Couple;
use App\Models\Event;
use App\Models\GiftAccount;
use App\Models\Guest;
use App\Models\GuestbookMessage;
use App\Models\Invitation;
use App\Models\Section;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

/**
 * Read-only activity feed for an invitation. Lists DB-side events from the
 * Spatie Activity Log (the LogsActivity trait on each model emits these on
 * create/update/delete). Admin-only tab — couples never see this.
 */
final class ActivityTab extends Component
{
    use WithPagination;

    public int $invitationId;

    /** Filter by activity log_name (e.g. invitation, section, couple, …). */
    public string $logFilter = '';

    /** Filter by causer user id. */
    public ?int $userFilter = null;

    public function updatingLogFilter(): void
    {
        $this->resetPage();
    }

    public function updatingUserFilter(): void
    {
        $this->resetPage();
    }

    public function mount(int $invitationId): void
    {
        $this->invitationId = $invitationId;
    }

    public function render(): View
    {
        $invitation = Invitation::query()->findOrFail($this->invitationId);
        Gate::authorize('update', $invitation);

        // We track activity on the Invitation row directly, plus on any child
        // row whose foreign key points back to this invitation. Children have
        // an invitation_id column, so we can filter via that on subject_type
        // + subject_id pairings.
        $childIds = [
            Section::class => Section::query()->where('invitation_id', $invitation->id)->pluck('id')->all(),
            Couple::class => Couple::query()->where('invitation_id', $invitation->id)->pluck('id')->all(),
            Event::class => Event::query()->where('invitation_id', $invitation->id)->pluck('id')->all(),
            Guest::class => Guest::query()->where('invitation_id', $invitation->id)->pluck('id')->all(),
            GiftAccount::class => GiftAccount::query()->where('invitation_id', $invitation->id)->pluck('id')->all(),
            GuestbookMessage::class => GuestbookMessage::query()->where('invitation_id', $invitation->id)->pluck('id')->all(),
        ];

        $query = Activity::query()
            ->where(function ($q) use ($invitation, $childIds) {
                $q->where(function ($qq) use ($invitation) {
                    $qq->where('subject_type', Invitation::class)
                        ->where('subject_id', $invitation->id);
                });
                foreach ($childIds as $class => $ids) {
                    if ($ids === []) {
                        continue;
                    }
                    $q->orWhere(function ($qq) use ($class, $ids) {
                        $qq->where('subject_type', $class)
                            ->whereIn('subject_id', $ids);
                    });
                }
            })
            ->when($this->logFilter !== '', fn ($q) => $q->where('log_name', $this->logFilter))
            ->when($this->userFilter !== null, fn ($q) => $q->where('causer_id', $this->userFilter))
            ->with('causer')
            ->latest();

        $activities = $query->paginate(30);

        // Distinct log names (for filter dropdown)
        $logNames = Activity::query()
            ->whereIn('subject_id', collect($childIds)->flatten()->push($invitation->id)->all())
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name')
            ->filter()
            ->values()
            ->all();

        // Distinct causers (admin / couple)
        $causers = Activity::query()
            ->whereIn('subject_id', collect($childIds)->flatten()->push($invitation->id)->all())
            ->whereNotNull('causer_id')
            ->with('causer')
            ->get()
            ->map(fn ($a) => $a->causer)
            ->filter()
            ->unique('id')
            ->values();

        return view('livewire.invitations.partials.tab-activity', [
            'activities' => $activities,
            'logNames' => $logNames,
            'causers' => $causers,
        ]);
    }
}
