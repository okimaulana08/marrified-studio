<?php

declare(strict_types=1);

use App\Models\Couple;
use App\Models\Event;
use App\Models\GiftAccount;
use App\Models\Guest;
use App\Models\GuestbookMessage;
use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\Section;

it('eager-loads all invitation relations without errors', function () {
    $invitation = Invitation::factory()->create();
    Couple::factory()->create(['invitation_id' => $invitation->id]);
    Event::factory()->akad()->create(['invitation_id' => $invitation->id]);
    Event::factory()->resepsi()->create(['invitation_id' => $invitation->id]);
    Guest::factory()->count(3)->create(['invitation_id' => $invitation->id]);
    Section::factory()->create(['invitation_id' => $invitation->id]);
    GiftAccount::factory()->create(['invitation_id' => $invitation->id]);
    GuestbookMessage::factory()->create(['invitation_id' => $invitation->id]);
    Rsvp::factory()->create(['invitation_id' => $invitation->id]);

    $loaded = Invitation::query()->with([
        'couple', 'events', 'guests', 'sections',
        'giftAccounts', 'guestbookMessages', 'rsvps',
    ])->find($invitation->id);

    expect($loaded)->not->toBeNull()
        ->and($loaded->couple)->not->toBeNull()
        ->and($loaded->events)->toHaveCount(2)
        ->and($loaded->guests)->toHaveCount(3)
        ->and($loaded->sections)->toHaveCount(1)
        ->and($loaded->giftAccounts)->toHaveCount(1)
        ->and($loaded->guestbookMessages)->toHaveCount(1)
        ->and($loaded->rsvps)->toHaveCount(1);
});

it('cascades delete from invitation to all dependents', function () {
    $invitation = Invitation::factory()->create();
    Couple::factory()->create(['invitation_id' => $invitation->id]);
    Guest::factory()->count(2)->create(['invitation_id' => $invitation->id]);
    Event::factory()->create(['invitation_id' => $invitation->id]);

    $invitation->delete();

    expect(Couple::query()->where('invitation_id', $invitation->id)->count())->toBe(0)
        ->and(Guest::query()->where('invitation_id', $invitation->id)->count())->toBe(0)
        ->and(Event::query()->where('invitation_id', $invitation->id)->count())->toBe(0);
});

it('generates unique guest tokens', function () {
    $invitation = Invitation::factory()->create();
    $guests = Guest::factory()->count(50)->create(['invitation_id' => $invitation->id]);

    expect($guests->pluck('token')->unique())->toHaveCount(50);
});
