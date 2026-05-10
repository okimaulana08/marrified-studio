<?php

declare(strict_types=1);

namespace App\Livewire\Invitations;

use App\Livewire\Invitations\Forms\GuestForm;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\Invitations\GuestCsvImporter;
use App\Support\GuestToken;
use App\Support\PhoneNumber;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public string $waTemplate = '';

    public bool $showTemplateEditor = false;

    public const DEFAULT_WA_TEMPLATE = "Halo {nama},\n\nDengan hormat kami mengundang Anda ke pernikahan kami:\n\n*{bride} & {groom}*\n📅 {tanggal}\n\nDetail undangan & konfirmasi kehadiran:\n{link}\n\nTerima kasih atas doa restunya 🙏";

    public function mount(int $invitationId, bool $isAdmin = false): void
    {
        $this->invitationId = $invitationId;
        $this->isAdmin = $isAdmin;

        $invitation = Invitation::query()->find($invitationId);
        $this->waTemplate = (string) ($invitation?->wa_broadcast_template ?? self::DEFAULT_WA_TEMPLATE);
    }

    public function saveWaTemplate(): void
    {
        $invitation = $this->resolveInvitation();
        $invitation->update(['wa_broadcast_template' => $this->waTemplate]);
        $this->flash('Template pesan disimpan.', 'success');
    }

    public function resetWaTemplate(): void
    {
        $this->waTemplate = self::DEFAULT_WA_TEMPLATE;
        $this->saveWaTemplate();
    }

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

    /**
     * Resolve the WA broadcast message for a specific guest by substituting
     * placeholders. Returns plain text suitable for clipboard or wa.me text param.
     */
    public function messageFor(Guest $guest, Invitation $invitation): string
    {
        $couple = $invitation->couple;
        $firstEvent = $invitation->events->sortBy('sort_order')->first();
        $tanggal = $firstEvent?->date?->translatedFormat('l, d F Y') ?? '';

        $link = url('/'.$invitation->slug.'/'.$guest->token);

        $replacements = [
            '{nama}' => $guest->name,
            '{bride}' => $couple?->bride_nickname ?: ($couple?->bride_name ?? ''),
            '{groom}' => $couple?->groom_nickname ?: ($couple?->groom_name ?? ''),
            '{tanggal}' => $tanggal,
            '{link}' => $link,
        ];

        $template = $this->waTemplate !== '' ? $this->waTemplate : self::DEFAULT_WA_TEMPLATE;

        return strtr($template, $replacements);
    }

    /**
     * Stream the guest list as a CSV with the resolved per-guest WA message
     * (handy for couples who use an external broadcast tool like WaBlas).
     */
    public function exportCsv(): StreamedResponse
    {
        $invitation = $this->resolveInvitation();
        $invitation->load(['couple', 'events']);

        $guests = Guest::query()
            ->where('invitation_id', $invitation->id)
            ->orderBy('name')
            ->get();

        $filename = 'tamu-'.$invitation->slug.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($guests, $invitation) {
            $out = fopen('php://output', 'w');
            // BOM agar Excel buka UTF-8 dengan benar
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['name', 'phone', 'token_url', 'message']);

            foreach ($guests as $guest) {
                fputcsv($out, [
                    $guest->name,
                    PhoneNumber::normalize($guest->phone) ?? ($guest->phone ?? ''),
                    url('/'.$invitation->slug.'/'.$guest->token),
                    $this->messageFor($guest, $invitation),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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

        $invitation = Invitation::query()->with(['couple', 'events'])->find($this->invitationId);
        $invitationSlug = $invitation?->slug ?? '';

        $paginated = $query->paginate(50);

        // Pre-compute WA link + resolved message per visible guest so the blade
        // stays clean and we don't re-resolve the template per render call.
        $guestActions = [];
        if ($invitation !== null) {
            foreach ($paginated->items() as $g) {
                $message = $this->messageFor($g, $invitation);
                $guestActions[$g->id] = [
                    'message' => $message,
                    'wa_link' => PhoneNumber::waLink($g->phone, $message),
                ];
            }
        }

        return view('livewire.invitations.guests-tab', [
            'guests' => $paginated,
            'invitationSlug' => $invitationSlug,
            'guestActions' => $guestActions,
        ]);
    }
}
