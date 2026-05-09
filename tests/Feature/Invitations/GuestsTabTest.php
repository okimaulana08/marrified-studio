<?php

declare(strict_types=1);

use App\Livewire\Invitations\GuestsTab;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
    $this->invitation = Invitation::factory()->create();
});

it('renders empty state when no guests exist', function () {
    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])->assertSee('Belum ada tamu');
});

it('adds a guest with auto-generated token', function () {
    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->set('form.name', 'Budi Hartono')
        ->set('form.relation', 'Bapak')
        ->set('form.phone', '0812')
        ->call('addGuest')
        ->assertSet('flashType', 'success')
        ->assertSet('form.name', '');

    $guest = Guest::query()->where('invitation_id', $this->invitation->id)->firstOrFail();
    expect($guest->name)->toBe('Budi Hartono')
        ->and($guest->relation)->toBe('Bapak')
        ->and($guest->token)->toHaveLength(10);
});

it('rejects guest without name', function () {
    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->set('form.name', '')
        ->call('addGuest')
        ->assertHasErrors(['form.name']);
});

it('searches guests by name and phone', function () {
    Guest::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Budi', 'phone' => '0812']);
    Guest::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Sari', 'phone' => '0813']);

    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->set('search', 'sari')
        ->assertSee('Sari')
        ->assertDontSee('Budi');
});

it('edits a guest via modal', function () {
    $guest = Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Original',
        'relation' => 'Bapak',
    ]);

    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->call('startEdit', $guest->id)
        ->assertSet('showEditModal', true)
        ->assertSet('form.name', 'Original')
        ->set('form.name', 'Updated Name')
        ->call('saveEdit')
        ->assertSet('showEditModal', false);

    expect($guest->fresh()->name)->toBe('Updated Name');
});

it('deletes a guest with confirmation', function () {
    $guest = Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Goodbye',
    ]);

    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->call('confirmDelete', $guest->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteTargetName', 'Goodbye')
        ->call('deleteGuest');

    expect(Guest::query()->find($guest->id))->toBeNull();
});

it('previews CSV upload before import', function () {
    $csv = UploadedFile::fake()->createWithContent('guests.csv',
        "name,relation,phone\nA,Bapak,0812\nB,Ibu,0813\n,X,0814"
    );

    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->call('openCsvModal')
        ->set('csvFile', $csv)
        ->call('previewCsv')
        ->assertSet('csvParsed', true);

    // Should have parsed 3 rows (2 valid, 1 with name error).
    $rows = Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->call('openCsvModal')
        ->set('csvFile', UploadedFile::fake()->createWithContent('guests.csv',
            "name,relation,phone\nA,Bapak,0812\nB,Ibu,0813\n,X,0814"))
        ->call('previewCsv')
        ->get('csvPreview');

    expect($rows)->toHaveCount(3)
        ->and($rows[2]['errors'])->not->toBe([]);
});

it('imports valid rows and skips invalid ones', function () {
    $csv = UploadedFile::fake()->createWithContent('guests.csv',
        "name,relation,phone\nA,Bapak,0812\n,X,0813\nB,Ibu,0814"
    );

    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->call('openCsvModal')
        ->set('csvFile', $csv)
        ->call('previewCsv')
        ->call('confirmImport')
        ->assertSet('showCsvModal', false);

    // 2 valid rows imported, 1 skipped due to empty name.
    expect(Guest::query()->where('invitation_id', $this->invitation->id)->count())->toBe(2);
});

it('forbids couple from acting on another invitation', function () {
    $couple = User::factory()->couple()->create();
    $this->actingAs($couple);

    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => false,
    ])
        ->set('form.name', 'Sneaky')
        ->call('addGuest')
        ->assertForbidden();
});

it('couple owner can add guests to their own invitation', function () {
    $couple = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $couple->id]);

    $this->actingAs($couple);

    Livewire::test(GuestsTab::class, [
        'invitationId' => $invitation->id,
        'isAdmin' => false,
    ])
        ->set('form.name', 'Tamu pertamaku')
        ->call('addGuest')
        ->assertSet('flashType', 'success');

    expect(Guest::query()->where('invitation_id', $invitation->id)->count())->toBe(1);
});
