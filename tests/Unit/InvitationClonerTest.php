<?php

declare(strict_types=1);

use App\Models\Couple;
use App\Models\Event;
use App\Models\GiftAccount;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Section;
use App\Models\User;
use App\Services\Invitations\InvitationCloner;
use App\Support\GuestToken;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('invitation_media');
    $this->cloner = new InvitationCloner;
});

function buildSourceInvitation(): Invitation
{
    $owner = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create([
        'slug' => 'source-couple',
        'user_id' => $owner->id,
        'religious_text' => ['ayat' => 'A', 'translation' => 'B', 'source' => 'C'],
    ]);

    Couple::factory()->create([
        'invitation_id' => $invitation->id,
        'bride_name' => 'Sari',
        'groom_name' => 'Budi',
        'bride_photo_path' => "{$invitation->id}/couple/bride.jpg",
        'groom_photo_path' => "{$invitation->id}/couple/groom.jpg",
    ]);

    Event::factory()->create([
        'invitation_id' => $invitation->id,
        'type' => 'akad', 'name' => 'Akad', 'date' => '2027-09-15',
        'venue_name' => 'Masjid', 'sort_order' => 0,
    ]);

    Section::factory()->create([
        'invitation_id' => $invitation->id,
        'type' => 'cover', 'variant' => 'arch', 'sort_order' => 0, 'enabled' => true,
    ]);

    GiftAccount::factory()->create([
        'invitation_id' => $invitation->id,
        'type' => 'bank', 'bank_name' => 'BCA',
        'account_number' => '111', 'account_name' => 'X', 'sort_order' => 0,
    ]);

    Guest::factory()->create([
        'invitation_id' => $invitation->id,
        'name' => 'Pak Hartono', 'relation' => 'Bapak', 'phone' => '0812',
        'token' => GuestToken::ensureUnique(), 'opens_count' => 5,
    ]);

    Storage::disk('invitation_media')->put("{$invitation->id}/couple/bride.jpg", 'fake-bride');
    Storage::disk('invitation_media')->put("{$invitation->id}/couple/groom.jpg", 'fake-groom');

    return $invitation->fresh(['couple', 'events', 'sections', 'giftAccounts', 'guests']);
}

it('rejects invalid target slug', function () {
    $source = buildSourceInvitation();
    expect(fn () => $this->cloner->clone($source, 'INVALID UPPER'))
        ->toThrow(RuntimeException::class, 'Invalid slug');
});

it('rejects same slug as source', function () {
    $source = buildSourceInvitation();
    expect(fn () => $this->cloner->clone($source, $source->slug))
        ->toThrow(RuntimeException::class, 'berbeda');
});

it('rejects target slug already taken', function () {
    $source = buildSourceInvitation();
    Invitation::factory()->create(['slug' => 'taken']);
    expect(fn () => $this->cloner->clone($source, 'taken'))
        ->toThrow(RuntimeException::class, 'already taken');
});

it('clones invitation with new slug, null user_id, copied content', function () {
    $source = buildSourceInvitation();
    $clone = $this->cloner->clone($source, 'cloned-couple');

    expect($clone->slug)->toBe('cloned-couple')
        ->and($clone->user_id)->toBeNull()
        ->and($clone->theme_slug)->toBe($source->theme_slug)
        ->and($clone->religion_type)->toBe($source->religion_type)
        ->and($clone->religious_text)->toBe($source->religious_text);
});

it('clones the couple and rewrites photo paths to new invitation id', function () {
    $source = buildSourceInvitation();
    $clone = $this->cloner->clone($source, 'cloned-couple');

    expect($clone->couple)->not->toBeNull()
        ->and($clone->couple->bride_name)->toBe('Sari')
        ->and($clone->couple->bride_photo_path)->toBe("{$clone->id}/couple/bride.jpg")
        ->and($clone->couple->groom_photo_path)->toBe("{$clone->id}/couple/groom.jpg");
});

it('duplicates media files into the clone directory', function () {
    $source = buildSourceInvitation();
    $clone = $this->cloner->clone($source, 'cloned-couple');

    Storage::disk('invitation_media')->assertExists("{$clone->id}/couple/bride.jpg");
    Storage::disk('invitation_media')->assertExists("{$clone->id}/couple/groom.jpg");

    // Source media must remain untouched.
    Storage::disk('invitation_media')->assertExists("{$source->id}/couple/bride.jpg");
});

it('clones events, sections, gift accounts, guests', function () {
    $source = buildSourceInvitation();
    $clone = $this->cloner->clone($source, 'cloned-couple');

    expect($clone->events)->toHaveCount(1)
        ->and($clone->sections)->toHaveCount(1)
        ->and($clone->giftAccounts)->toHaveCount(1)
        ->and($clone->guests)->toHaveCount(1);
});

it('regenerates guest tokens and resets opens_count', function () {
    $source = buildSourceInvitation();
    $sourceToken = $source->guests->first()->token;

    $clone = $this->cloner->clone($source, 'cloned-couple');
    $cloneGuest = $clone->guests->first();

    expect($cloneGuest->token)->not->toBe($sourceToken)
        ->and($cloneGuest->token)->toHaveLength(10)
        ->and($cloneGuest->opens_count)->toBe(0)
        ->and($cloneGuest->first_opened_at)->toBeNull();
});

it('does not carry over user link on clone', function () {
    $source = buildSourceInvitation();
    expect($source->user_id)->not->toBeNull(); // sanity: source has user

    $clone = $this->cloner->clone($source, 'cloned-couple');

    expect($clone->user_id)->toBeNull();
});
