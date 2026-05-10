<?php

declare(strict_types=1);

namespace App\Livewire\Invitations;

use App\Models\Guest;
use App\Models\GuestbookMessage;
use App\Models\Invitation;
use App\Models\Rsvp;
use App\Support\PhoneNumber;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Read-only analytics dashboard for an invitation. Mounted as a child component
 * under the InvitationEditor tab nav (same pattern as GuestsTab). Queries pure
 * DB aggregates — no extra tracking columns needed, everything reuses the
 * `guests.opens_count` + `first_opened_at` data and the RSVP table.
 */
final class AnalyticsTab extends Component
{
    public int $invitationId;

    public bool $isAdmin = false;

    public function mount(int $invitationId, bool $isAdmin = false): void
    {
        $this->invitationId = $invitationId;
        $this->isAdmin = $isAdmin;
    }

    public function render(): View
    {
        $invitation = Invitation::query()->findOrFail($this->invitationId);
        Gate::authorize('update', $invitation);

        $guestsBase = Guest::query()->where('invitation_id', $invitation->id);

        $totalGuests = (clone $guestsBase)->count();
        $totalOpens = (int) (clone $guestsBase)->sum('opens_count');
        $uniqueOpened = (clone $guestsBase)->where('opens_count', '>', 0)->count();
        $percentOpened = $totalGuests > 0 ? (int) round(($uniqueOpened / $totalGuests) * 100) : 0;

        $notOpenedYet = (clone $guestsBase)
            ->where('opens_count', 0)
            ->orderBy('name')
            ->take(50)
            ->get(['id', 'name', 'phone', 'token']);

        $topOpeners = (clone $guestsBase)
            ->where('opens_count', '>', 0)
            ->orderByDesc('opens_count')
            ->take(5)
            ->get(['id', 'name', 'opens_count', 'last_opened_at']);

        $rsvpBase = Rsvp::query()->where('invitation_id', $invitation->id);
        $rsvpCounts = (clone $rsvpBase)
            ->selectRaw('attendance, COUNT(*) as count, COALESCE(SUM(party_size), 0) as party')
            ->groupBy('attendance')
            ->get()
            ->keyBy('attendance');

        $attendingCount = (int) ($rsvpCounts['attending']->count ?? 0);
        $notAttendingCount = (int) ($rsvpCounts['not_attending']->count ?? 0);
        $maybeCount = (int) ($rsvpCounts['maybe']->count ?? 0);
        $totalRsvp = $attendingCount + $notAttendingCount + $maybeCount;
        $estimatedHeadcount = (int) ($rsvpCounts['attending']->party ?? 0);

        $guestbookCount = GuestbookMessage::query()
            ->where('invitation_id', $invitation->id)
            ->where('is_visible', true)
            ->count();

        // Open histogram — last 14 days based on first_opened_at.
        $cutoff = Carbon::now()->subDays(13)->startOfDay();
        $opensByDay = (clone $guestsBase)
            ->whereNotNull('first_opened_at')
            ->where('first_opened_at', '>=', $cutoff)
            ->selectRaw('DATE(first_opened_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();

        $histogramCategories = [];
        $histogramValues = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->format('Y-m-d');
            $histogramCategories[] = Carbon::parse($d)->format('d/m');
            $histogramValues[] = (int) ($opensByDay[$d] ?? 0);
        }

        $slug = $invitation->slug;

        // Pre-compute WA reminder link per "belum buka" guest, reusing the
        // invitation's WA template (or default fallback).
        $template = (string) ($invitation->wa_broadcast_template
            ?? GuestsTab::DEFAULT_WA_TEMPLATE);

        $invitation->loadMissing(['couple', 'events']);
        $couple = $invitation->couple;
        $firstEvent = $invitation->events->sortBy('sort_order')->first();
        $tanggal = $firstEvent?->date?->translatedFormat('l, d F Y') ?? '';

        $reminderActions = [];
        foreach ($notOpenedYet as $g) {
            $link = url('/'.$slug.'/'.$g->token);
            $message = strtr($template, [
                '{nama}' => $g->name,
                '{bride}' => $couple?->bride_nickname ?: ($couple?->bride_name ?? ''),
                '{groom}' => $couple?->groom_nickname ?: ($couple?->groom_name ?? ''),
                '{tanggal}' => $tanggal,
                '{link}' => $link,
            ]);
            $reminderActions[$g->id] = [
                'wa_link' => PhoneNumber::waLink($g->phone, $message),
            ];
        }

        return view('livewire.invitations.partials.tab-analytics', [
            'totalGuests' => $totalGuests,
            'totalOpens' => $totalOpens,
            'uniqueOpened' => $uniqueOpened,
            'percentOpened' => $percentOpened,
            'attendingCount' => $attendingCount,
            'notAttendingCount' => $notAttendingCount,
            'maybeCount' => $maybeCount,
            'totalRsvp' => $totalRsvp,
            'estimatedHeadcount' => $estimatedHeadcount,
            'guestbookCount' => $guestbookCount,
            'notOpenedYet' => $notOpenedYet,
            'topOpeners' => $topOpeners,
            'histogramCategories' => $histogramCategories,
            'histogramValues' => $histogramValues,
            'reminderActions' => $reminderActions,
            'invitationSlug' => $slug,
        ]);
    }
}
