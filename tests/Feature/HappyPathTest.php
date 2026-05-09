<?php

declare(strict_types=1);

use App\Livewire\Admin\Invitations\CredentialManager;
use App\Livewire\Admin\Invitations\InvitationList;
use App\Livewire\Invitations\GuestsTab;
use App\Livewire\Invitations\InvitationEditor;
use App\Models\Couple;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Section;
use App\Models\User;
use App\Support\GuestToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('admin builds invitation, couple logs in, fills tabs, guest opens via token', function () {
    Storage::fake('invitation_media');

    /* ─────────── 1. Admin logs in ─────────── */
    $admin = User::factory()->admin()->create([
        'email' => 'studio@marrified.test',
        'password' => 'admin-pass',
    ]);

    $this->post('/login', [
        'email' => 'studio@marrified.test',
        'password' => 'admin-pass',
    ])->assertRedirect(route('admin.invitations.index'));

    /* ─────────── 2. Admin creates an invitation ─────────── */
    Livewire::test(InvitationEditor::class)
        ->set('basic.slug', 'happy-path')
        ->set('basic.themeSlug', 'watercolor-lush')
        ->set('basic.religionType', 'islam')
        ->call('save')
        ->assertRedirect(route('invitations.edit', 'happy-path'));

    $invitation = Invitation::query()->where('slug', 'happy-path')->firstOrFail();

    expect($invitation->sections()->count())->toBe(8) // auto-seeded
        ->and($invitation->user_id)->toBeNull(); // no credentials yet

    /* ─────────── 3. Admin issues couple credentials ─────────── */
    $component = Livewire::test(CredentialManager::class, ['slug' => 'happy-path'])
        ->set('email', 'happy@couple.test')
        ->call('issue')
        ->assertSet('flashType', 'success');

    $couplePassword = $component->get('freshPlaintext');
    expect($couplePassword)->toBeString()->and(strlen($couplePassword))->toBe(12);

    auth()->logout();

    /* ─────────── 4. Couple logs in with issued credentials ─────────── */
    $this->post('/login', [
        'email' => 'happy@couple.test',
        'password' => $couplePassword,
    ])->assertRedirect(route('invitations.edit', 'happy-path'));

    expect(auth()->user()->email)->toBe('happy@couple.test');

    /* ─────────── 5. Couple fills couple + events tabs ─────────── */
    $invitation = $invitation->fresh();
    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'Sari Dewi')
        ->set('couple.groomName', 'Budi Santoso')
        ->set('bridePhoto', UploadedFile::fake()->image('bride.jpg', 600, 600))
        ->call('saveCouple')
        ->assertSet('flashType', 'success');

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh()])
        ->call('addEventRow')
        ->set('events.rows.0.type', 'akad')
        ->set('events.rows.0.name', 'Akad Nikah')
        ->set('events.rows.0.date', '2027-09-15')
        ->set('events.rows.0.venue_name', 'Masjid Al-Falah')
        ->call('saveEvents')
        ->assertSet('flashType', 'success');

    /* ─────────── 6. Couple adds guests ─────────── */
    Livewire::test(GuestsTab::class, [
        'invitationId' => $invitation->id,
        'isAdmin' => false,
    ])
        ->set('form.name', 'Pak Hartono')
        ->set('form.relation', 'Bapak')
        ->set('form.phone', '0812')
        ->call('addGuest')
        ->assertSet('flashType', 'success');

    $guest = Guest::query()->where('invitation_id', $invitation->id)->firstOrFail();
    expect($guest->name)->toBe('Pak Hartono')
        ->and($guest->token)->toHaveLength(10);

    /* ─────────── 7. Couple cannot peek at admin or other invitations ─────────── */
    $this->get(route('admin.invitations.index'))->assertForbidden();
    $this->get(route('admin.themes.index'))->assertForbidden();

    $other = Invitation::factory()->create();
    $this->get(route('invitations.edit', $other->slug))->assertForbidden();

    /* ─────────── 8. Guest opens via token URL → opens_count ticks up ─────────── */
    auth()->logout();

    $this->get("/happy-path/{$guest->token}")
        ->assertOk()
        ->assertSee('Sari Dewi')
        ->assertSee('Pak Hartono');

    expect($guest->fresh()->opens_count)->toBe(1)
        ->and($guest->fresh()->first_opened_at)->not->toBeNull();
});

it('admin clones an existing invitation; clone has no user link and fresh tokens', function () {
    Storage::fake('invitation_media');
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    /* Set up source invitation with full content */
    $source = Invitation::factory()->create(['slug' => 'source-couple']);
    Couple::factory()->create([
        'invitation_id' => $source->id,
        'bride_name' => 'A', 'groom_name' => 'B',
    ]);
    Section::factory()->create([
        'invitation_id' => $source->id,
        'type' => 'cover', 'variant' => 'arch',
        'sort_order' => 0, 'enabled' => true,
    ]);
    Guest::factory()->create([
        'invitation_id' => $source->id,
        'name' => 'Tamu A', 'relation' => 'Bapak', 'phone' => '0812',
        'token' => GuestToken::ensureUnique(),
        'opens_count' => 5,
    ]);

    /* Clone via InvitationList Livewire */
    Livewire::test(InvitationList::class)
        ->call('openCloneModal', $source->id)
        ->set('cloneTargetSlug', 'cloned-couple')
        ->call('confirmClone')
        ->assertRedirect(route('invitations.edit', 'cloned-couple'));

    $clone = Invitation::query()->where('slug', 'cloned-couple')->firstOrFail();
    expect($clone->user_id)->toBeNull()
        ->and($clone->theme_slug)->toBe($source->theme_slug);

    $clonedGuest = Guest::query()->where('invitation_id', $clone->id)->firstOrFail();
    $sourceGuestToken = Guest::query()->where('invitation_id', $source->id)->value('token');

    expect($clonedGuest->name)->toBe('Tamu A')
        ->and($clonedGuest->token)->not->toBe($sourceGuestToken)
        ->and($clonedGuest->opens_count)->toBe(0);
});

it('discard-tab reverts couple form to DB state', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $invitation = Invitation::factory()->create();
    Couple::factory()->create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Original Bride',
        'groom_name' => 'Original Groom',
    ]);

    $component = Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'Edited Bride')
        ->set('couple.groomName', 'Edited Groom')
        ->call('discardTab', 'couple');

    expect($component->get('couple.brideName'))->toBe('Original Bride')
        ->and($component->get('couple.groomName'))->toBe('Original Groom');
    $component->assertSet('flashType', 'info');
});
