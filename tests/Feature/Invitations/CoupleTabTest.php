<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Couple;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('invitation_media');
});

it('persists couple fields without photos', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'Sari Dewi')
        ->set('couple.brideNickname', 'Sari')
        ->set('couple.brideParents', 'Bapak A & Ibu B')
        ->set('couple.brideInstagram', '@sari')
        ->set('couple.groomName', 'Budi Santoso')
        ->set('couple.groomNickname', 'Budi')
        ->set('couple.groomParents', 'Bapak X & Ibu Y')
        ->call('saveCouple')
        ->assertSet('flashType', 'success');

    $couple = Couple::query()->where('invitation_id', $invitation->id)->first();
    expect($couple)
        ->not->toBeNull()
        ->bride_name->toBe('Sari Dewi')
        ->bride_nickname->toBe('Sari')
        ->groom_name->toBe('Budi Santoso')
        ->bride_photo_path->toBeNull()
        ->groom_photo_path->toBeNull();
});

it('uploads bride and groom photos to invitation_media disk', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'Sari')
        ->set('couple.groomName', 'Budi')
        ->set('bridePhoto', UploadedFile::fake()->image('b.jpg', 800, 800))
        ->set('groomPhoto', UploadedFile::fake()->image('g.png', 800, 800))
        ->call('saveCouple')
        ->assertSet('flashType', 'success')
        ->assertSet('bridePhoto', null)
        ->assertSet('groomPhoto', null);

    $couple = Couple::query()->where('invitation_id', $invitation->id)->firstOrFail();

    expect($couple->bride_photo_path)->toBe("{$invitation->id}/couple/bride.jpg")
        ->and($couple->groom_photo_path)->toBe("{$invitation->id}/couple/groom.png");

    Storage::disk('invitation_media')->assertExists("{$invitation->id}/couple/bride.jpg");
    Storage::disk('invitation_media')->assertExists("{$invitation->id}/couple/groom.png");
});

it('replaces previous photo when uploading a new one with different extension', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'Sari')
        ->set('couple.groomName', 'Budi')
        ->set('bridePhoto', UploadedFile::fake()->image('first.jpg', 800, 800))
        ->call('saveCouple');

    Storage::disk('invitation_media')->assertExists("{$invitation->id}/couple/bride.jpg");

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh()])
        ->set('bridePhoto', UploadedFile::fake()->image('second.png', 800, 800))
        ->call('saveCouple');

    Storage::disk('invitation_media')->assertMissing("{$invitation->id}/couple/bride.jpg");
    Storage::disk('invitation_media')->assertExists("{$invitation->id}/couple/bride.png");
});

it('rejects oversized photo upload', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('bridePhoto', UploadedFile::fake()->create('huge.jpg', 6000)) // 6 MB > 5 MB limit
        ->call('saveCouple')
        ->assertHasErrors('bridePhoto');
});

it('requires bride and groom names', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', '')
        ->set('couple.groomName', '')
        ->call('saveCouple')
        ->assertHasErrors(['couple.brideName', 'couple.groomName']);
});

it('couple owner can save their own couple data', function () {
    $couple = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $couple->id]);

    $this->actingAs($couple);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'My Name')
        ->set('couple.groomName', 'Their Name')
        ->call('saveCouple')
        ->assertSet('flashType', 'success');

    expect(Couple::query()->where('invitation_id', $invitation->id)->value('bride_name'))->toBe('My Name');
});

it('removeBridePhoto deletes file from disk and clears DB column', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin);

    // First upload a photo.
    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'X')
        ->set('couple.groomName', 'Y')
        ->set('bridePhoto', UploadedFile::fake()->image('b.jpg'))
        ->call('saveCouple');

    Storage::disk('invitation_media')->assertExists("{$invitation->id}/couple/bride.jpg");
    expect(Couple::query()->where('invitation_id', $invitation->id)->value('bride_photo_path'))
        ->toBe("{$invitation->id}/couple/bride.jpg");

    // Now remove it.
    Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh()])
        ->call('removeBridePhoto')
        ->assertSet('flashType', 'success')
        ->assertSet('couple.bridePhotoPath', null);

    Storage::disk('invitation_media')->assertMissing("{$invitation->id}/couple/bride.jpg");
    expect(Couple::query()->where('invitation_id', $invitation->id)->value('bride_photo_path'))->toBeNull();
});

it('removeGroomPhoto only touches groom column', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'X')
        ->set('couple.groomName', 'Y')
        ->set('bridePhoto', UploadedFile::fake()->image('b.jpg'))
        ->set('groomPhoto', UploadedFile::fake()->image('g.jpg'))
        ->call('saveCouple');

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh()])
        ->call('removeGroomPhoto');

    $couple = Couple::query()->where('invitation_id', $invitation->id)->firstOrFail();
    expect($couple->bride_photo_path)->toBe("{$invitation->id}/couple/bride.jpg")
        ->and($couple->groom_photo_path)->toBeNull();
});

it('removing a photo when none was set is a no-op', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'X')
        ->set('couple.groomName', 'Y')
        ->call('saveCouple');

    expect(fn () => Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh()])
        ->call('removeBridePhoto')
        ->assertSet('flashType', 'success'))->not->toThrow(Throwable::class);
});

it('media directory is removed when invitation is deleted', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('couple.brideName', 'X')
        ->set('couple.groomName', 'Y')
        ->set('bridePhoto', UploadedFile::fake()->image('b.jpg'))
        ->call('saveCouple');

    Storage::disk('invitation_media')->assertExists("{$invitation->id}/couple/bride.jpg");

    $invitation->delete();

    expect(Storage::disk('invitation_media')->directoryExists((string) $invitation->id))->toBeFalse();
});
