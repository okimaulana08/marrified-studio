<?php

declare(strict_types=1);

namespace App\Livewire\Invitations;

use App\Enums\ReligionType;
use App\Livewire\Invitations\Forms\CountdownForm;
use App\Livewire\Invitations\Forms\CoupleForm;
use App\Livewire\Invitations\Forms\EventsForm;
use App\Livewire\Invitations\Forms\GalleryForm;
use App\Livewire\Invitations\Forms\GiftAccountsForm;
use App\Livewire\Invitations\Forms\InvitationBasicForm;
use App\Livewire\Invitations\Forms\MusicForm;
use App\Livewire\Invitations\Forms\ReligiousTextForm;
use App\Livewire\Invitations\Forms\SectionsForm;
use App\Livewire\Invitations\Forms\StoriesForm;
use App\Livewire\Invitations\Forms\ThanksForm;
use App\Models\Invitation;
use App\Models\Section;
use App\Services\Invitations\CompletionAuditor;
use App\Services\Invitations\InvitationWriter;
use App\Services\Themes\ThemeRegistry;
use App\Services\Themes\VariantScanner;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public GalleryForm $gallery;

    /** Pending file picks for the Gallery tab. Cleared after each save. */
    public array $newGalleryPhotos = [];

    public StoriesForm $stories;

    /** Per-row TempUploadedFile keyed by row index. Cleared after saveStories. */
    public array $storyPhotos = [];

    public MusicForm $music;

    public ThanksForm $thanks;

    public CountdownForm $countdown;

    /** Pending file pick for thanks photo. Cleared after saveThanks. */
    #[Validate('nullable|image|max:5120')]
    public $thanksPhoto = null;

    /** Bumped after each save so iframe `?v=N` cache-busts and reloads. */
    public int $previewKey = 0;

    /** Completion percentage (0-100) shown by the gauge in the editor header. */
    public int $completionPercent = 0;

    /** List of pending todos for the gauge dropdown. */
    public array $completionTodos = [];

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

        $gallerySection = $invitation->sections->firstWhere('type', 'gallery');
        $this->gallery->fillFromSection($gallerySection);

        $storySection = $invitation->sections->firstWhere('type', 'story');
        $this->stories->fillFromSection($storySection);

        $this->music->fillFromModel($invitation);

        $thanksSection = $invitation->sections->firstWhere('type', 'thanks');
        $this->thanks->fillFromSection($thanksSection);

        $countdownSection = $invitation->sections->firstWhere('type', 'countdown');
        $this->countdown->fillFromSection($countdownSection);

        $this->refreshCompletion($invitation);
    }

    private function refreshCompletion(?Invitation $invitation = null): void
    {
        $invitation ??= Invitation::query()->find($this->invitationId);
        if ($invitation === null) {
            return;
        }
        $audit = app(CompletionAuditor::class)->audit($invitation);
        $this->completionPercent = $audit['percent'];
        $this->completionTodos = $audit['todos'];
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

    public function removeBridePhoto(): void
    {
        $this->removeCouplePhoto('bride');
    }

    public function removeGroomPhoto(): void
    {
        $this->removeCouplePhoto('groom');
    }

    private function removeCouplePhoto(string $role): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->couple->removePhoto($invitation, $role);

            // Also discard any pending temp upload for that role so the dropzone
            // returns to empty state immediately.
            if ($role === 'bride') {
                $this->bridePhoto = null;
            } else {
                $this->groomPhoto = null;
            }

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
     * Validate + store all newGalleryPhotos to disk, append to gallery form,
     * persist to Section.content. Enforces 20-image cap pre-store so we never
     * write past-cap files. Validation errors leave temp files orphaned in
     * livewire-tmp; Livewire cleans those up automatically.
     */
    public function uploadGalleryPhotos(): void
    {
        try {
            $invitation = $this->resolveInvitation();

            $this->validate([
                'newGalleryPhotos' => 'array|min:1',
                'newGalleryPhotos.*' => 'image|mimes:jpg,jpeg,png,webp|max:'.GalleryForm::MAX_FILE_KB,
            ]);

            $remaining = GalleryForm::MAX_IMAGES - count($this->gallery->images);
            if ($remaining <= 0) {
                $this->flash('Sudah maksimal '.GalleryForm::MAX_IMAGES.' foto. Hapus dulu yang ada.', 'error');

                return;
            }

            $toStore = array_slice($this->newGalleryPhotos, 0, $remaining);
            $skipped = max(0, count($this->newGalleryPhotos) - $remaining);

            foreach ($toStore as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    $this->gallery->appendUploaded($invitation, $file);
                }
            }

            $this->gallery->persist($invitation);
            $this->newGalleryPhotos = [];

            $message = count($toStore).' foto ditambahkan.';
            if ($skipped > 0) {
                $message .= " {$skipped} di-skip karena melewati batas ".GalleryForm::MAX_IMAGES.'.';
            }
            $this->flash($message, 'success');
            $this->previewKey++;
            $this->dispatch('invitation-saved');
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function removeGalleryPhoto(int $index): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->gallery->removeImage($index);
            $this->gallery->persist($invitation);
            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function moveGalleryUp(int $index): void
    {
        $this->gallery->moveUp($index);
    }

    public function moveGalleryDown(int $index): void
    {
        $this->gallery->moveDown($index);
    }

    public function saveGalleryOrder(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->gallery->persist($invitation);
            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function addStoryRow(): void
    {
        $this->stories->addRow();
    }

    public function removeStoryRow(int $index): void
    {
        // Hapus foto di disk dulu kalau ada
        $path = $this->stories->rows[$index]['photo_path'] ?? null;
        if ($path !== null) {
            Storage::disk('invitation_media')->delete($path);
        }
        $this->stories->removeRow($index);

        // Re-index pending photos
        unset($this->storyPhotos[$index]);
        $this->storyPhotos = array_values($this->storyPhotos);
    }

    public function moveStoryUp(int $index): void
    {
        $this->stories->moveUp($index);
        $this->swapStoryPhoto($index, $index - 1);
    }

    public function moveStoryDown(int $index): void
    {
        $this->stories->moveDown($index);
        $this->swapStoryPhoto($index, $index + 1);
    }

    private function swapStoryPhoto(int $a, int $b): void
    {
        $aHas = array_key_exists($a, $this->storyPhotos);
        $bHas = array_key_exists($b, $this->storyPhotos);
        if (! $aHas && ! $bHas) {
            return;
        }
        $aVal = $this->storyPhotos[$a] ?? null;
        $bVal = $this->storyPhotos[$b] ?? null;
        $this->storyPhotos[$a] = $bVal;
        $this->storyPhotos[$b] = $aVal;
    }

    public function removeStoryPhoto(int $index): void
    {
        $path = $this->stories->rows[$index]['photo_path'] ?? null;
        if ($path !== null) {
            Storage::disk('invitation_media')->delete($path);
        }
        if (isset($this->stories->rows[$index])) {
            $this->stories->rows[$index]['photo_path'] = null;
        }
        unset($this->storyPhotos[$index]);
    }

    public function saveThanks(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->validate(['thanksPhoto' => 'nullable|image|max:5120']);
            $this->thanks->validate();

            $photo = $this->thanksPhoto instanceof TemporaryUploadedFile ? $this->thanksPhoto : null;
            $this->thanks->persist($invitation, $photo);
            $this->thanksPhoto = null;

            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function removeThanksPhoto(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->thanks->removePhoto($invitation);
            $this->thanksPhoto = null;
            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function saveCountdown(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->countdown->validate();
            $this->countdown->persist($invitation);
            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function saveMusic(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->music->validate();
            $this->music->persist($invitation);
            $this->flashSaved();
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function saveStories(): void
    {
        try {
            $invitation = $this->resolveInvitation();
            $this->stories->validate();

            // Validate any pending photos
            $photoRules = [];
            foreach ($this->storyPhotos as $i => $photo) {
                if ($photo !== null) {
                    $photoRules["storyPhotos.{$i}"] = 'image|mimes:jpg,jpeg,png,webp|max:5120';
                }
            }
            if ($photoRules !== []) {
                $this->validate($photoRules);
            }

            // Store new photos and update photo_path on rows
            foreach ($this->storyPhotos as $i => $photo) {
                if (! ($photo instanceof TemporaryUploadedFile)) {
                    continue;
                }
                if (! isset($this->stories->rows[$i])) {
                    continue;
                }

                $previous = $this->stories->rows[$i]['photo_path'] ?? null;
                if ($previous !== null) {
                    Storage::disk('invitation_media')->delete($previous);
                }

                $ext = strtolower($photo->getClientOriginalExtension() ?: 'jpg');
                $dir = "{$invitation->id}/story";
                $filename = (string) Str::ulid().'.'.$ext;
                $photo->storeAs($dir, $filename, 'invitation_media');

                $this->stories->rows[$i]['photo_path'] = "{$dir}/{$filename}";
            }

            $this->stories->persist($invitation);
            $this->storyPhotos = [];

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
            'gallery' => $this->gallery->fillFromSection(
                Section::query()->where('invitation_id', $invitation->id)->where('type', 'gallery')->first()
            ),
            'stories' => $this->stories->fillFromSection(
                Section::query()->where('invitation_id', $invitation->id)->where('type', 'story')->first()
            ),
            'music' => $this->music->fillFromModel($invitation),
            'thanks' => $this->thanks->fillFromSection(
                Section::query()->where('invitation_id', $invitation->id)->where('type', 'thanks')->first()
            ),
            'countdown' => $this->countdown->fillFromSection(
                Section::query()->where('invitation_id', $invitation->id)->where('type', 'countdown')->first()
            ),
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
        $this->refreshCompletion();
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
