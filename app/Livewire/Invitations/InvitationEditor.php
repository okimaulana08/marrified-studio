<?php

declare(strict_types=1);

namespace App\Livewire\Invitations;

use App\Enums\ReligionType;
use App\Livewire\Invitations\Forms\CoupleForm;
use App\Livewire\Invitations\Forms\EventsForm;
use App\Livewire\Invitations\Forms\GiftAccountsForm;
use App\Livewire\Invitations\Forms\InvitationBasicForm;
use App\Livewire\Invitations\Forms\ReligiousTextForm;
use App\Livewire\Invitations\Forms\SectionsForm;
use App\Models\Invitation;
use App\Services\Invitations\InvitationWriter;
use App\Services\Themes\ThemeRegistry;
use App\Services\Themes\VariantScanner;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

/**
 * Shell component for the invitation editor.
 *
 * Modes:
 *  - Create: $invitation null. Only Basic tab visible. save() creates the row
 *    and redirects to /invitations/{slug}/edit.
 *  - Edit: $invitation passed. All tabs visible (slug/theme read-only for
 *    couple users). Each tab persists via its own action method below.
 */
final class InvitationEditor extends Component
{
    use WithFileUploads;

    public ?int $invitationId = null;

    public string $slug = '';

    public bool $isAdmin = false;

    public bool $isNew = true;

    public InvitationBasicForm $basic;

    public CoupleForm $couple;

    public EventsForm $events;

    public ReligiousTextForm $religious;

    public SectionsForm $sections;

    public GiftAccountsForm $gift;

    /** Bumped after each save so iframe `?v=N` cache-busts and reloads. */
    public int $previewKey = 0;

    /** Live-uploaded photos. Reset to null after each successful saveCouple. */
    #[Validate('nullable|image|max:5120')]
    public $bridePhoto = null;

    #[Validate('nullable|image|max:5120')]
    public $groomPhoto = null;

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    public function mount(?Invitation $invitation = null): void
    {
        $this->isAdmin = (bool) Auth::user()?->isAdmin();

        if ($invitation === null) {
            if (! $this->isAdmin) {
                throw new AuthorizationException('Couple users cannot create invitations.');
            }
            $this->isNew = true;

            return;
        }

        Gate::authorize('update', $invitation);
        $invitation->load(['couple', 'events', 'sections', 'giftAccounts']);

        $this->invitationId = $invitation->id;
        $this->slug = $invitation->slug;
        $this->isNew = false;

        $this->basic->fillFromModel($invitation);
        $this->couple->fillFromModel($invitation->couple);
        $this->events->fillFromModel($invitation);
        $this->religious->fillFromModel($invitation);
        $this->sections->fillFromModel($invitation);
        $this->gift->fillFromModel($invitation);
    }

    public function save(): void
    {
        $writer = app(InvitationWriter::class);

        try {
            if ($this->isNew) {
                $this->basic->validate();
                $invitation = $writer->create($this->basic->toAttributes());

                $this->redirectRoute('invitations.edit', ['slug' => $invitation->slug], navigate: false);

                return;
            }

            $invitation = $this->resolveInvitation();
            $this->validateOnly('basic.religionType');

            $oldReligion = $invitation->religion_type;
            $invitation->update(['religion_type' => $this->basic->religionType]);

            // Religion changed → resync religious text keys so the next save
            // doesn't accidentally drop fields the new religion expects.
            if ($oldReligion !== $this->basic->religionType) {
                $this->religious->syncKeys(ReligionType::tryFrom($this->basic->religionType));
            }

            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function saveCouple(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->validate(['bridePhoto' => 'nullable|image|max:5120', 'groomPhoto' => 'nullable|image|max:5120']);
            $this->couple->validate();

            $bride = $this->bridePhoto instanceof TemporaryUploadedFile ? $this->bridePhoto : null;
            $groom = $this->groomPhoto instanceof TemporaryUploadedFile ? $this->groomPhoto : null;

            $this->couple->persist($invitation, $bride, $groom);

            $this->bridePhoto = null;
            $this->groomPhoto = null;

            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function addEventRow(): void
    {
        $this->events->addRow();
    }

    public function removeEventRow(int $index): void
    {
        $this->events->removeRow($index);
    }

    public function moveEventUp(int $index): void
    {
        $this->events->moveUp($index);
    }

    public function moveEventDown(int $index): void
    {
        $this->events->moveDown($index);
    }

    public function saveEvents(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->events->validate();
            $this->events->persist($invitation);

            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function saveReligious(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->religious->persist($invitation);

            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function moveSectionUp(int $index): void
    {
        $this->sections->moveUp($index);
    }

    public function moveSectionDown(int $index): void
    {
        $this->sections->moveDown($index);
    }

    public function saveSections(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->sections->validate();
            $this->sections->persist($invitation);

            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function addGiftRow(): void
    {
        $this->gift->addRow();
    }

    public function removeGiftRow(int $index): void
    {
        $this->gift->removeRow($index);
    }

    public function moveGiftUp(int $index): void
    {
        $this->gift->moveUp($index);
    }

    public function moveGiftDown(int $index): void
    {
        $this->gift->moveDown($index);
    }

    public function saveGift(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->gift->validate();
            $this->gift->persist($invitation);

            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    /**
     * Re-load a single tab's form from the DB, discarding any pending edits.
     * Used by the per-tab "Batalkan" buttons. No-op in create mode.
     */
    public function discardTab(string $tab): void
    {
        if ($this->isNew) {
            return;
        }

        $invitation = $this->resolveInvitation();
        $invitation->load(['couple', 'events', 'sections', 'giftAccounts']);

        match ($tab) {
            'basic' => $this->basic->fillFromModel($invitation),
            'couple' => $this->couple->fillFromModel($invitation->couple),
            'events' => $this->events->fillFromModel($invitation),
            'religious' => $this->religious->fillFromModel($invitation),
            'sections' => $this->sections->fillFromModel($invitation),
            'gift' => $this->gift->fillFromModel($invitation),
            default => null,
        };

        $this->resetErrorBag();
        $this->flash('Perubahan dibatalkan.', 'info');
    }

    private function resolveInvitation(): Invitation
    {
        $invitation = Invitation::query()->findOrFail($this->invitationId);
        Gate::authorize('update', $invitation);

        return $invitation;
    }

    private function flashSaved(): void
    {
        $this->flash('Disimpan.', 'success');
        $this->previewKey++;
        $this->dispatch('invitation-saved');
    }

    private function flash(string $message, string $type): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function render(): View
    {
        $themes = app(ThemeRegistry::class)->all();
        $religion = ReligionType::tryFrom($this->basic->religionType);
        $variantOptions = $this->isNew
            ? []
            : $this->sections->variantOptions(app(VariantScanner::class));

        return view('livewire.invitations.editor', [
            'themes' => $themes,
            'religionOptions' => ReligionType::cases(),
            'religiousFieldKeys' => $religion?->fieldKeys() ?? [],
            'religionLabel' => $religion?->label() ?? '—',
            'variantOptions' => $variantOptions,
        ]);
    }
}
