<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Couple;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Section;
use App\Models\User;
use App\Services\Invitations\InvitationWriter;
use App\Support\GuestToken;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();

    // Build a renderable invitation: minimum sections + couple + 1 event.
    $this->invitation = Invitation::factory()->create([
        'slug' => 'preview-test',
        'theme_slug' => 'watercolor-lush',
    ]);
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_name' => 'Sari',
        'groom_name' => 'Budi',
    ]);
    Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'akad',
        'name' => 'Akad',
        'date' => '2027-06-01',
        'venue_name' => 'V',
        'sort_order' => 0,
    ]);
    foreach (InvitationWriter::DEFAULT_SECTION_TYPES as $i => $type) {
        Section::factory()->create([
            'invitation_id' => $this->invitation->id,
            'type' => $type,
            'variant' => match ($type) {
                'cover' => 'arch', 'quotes' => 'default', 'couple' => 'side-by-side',
                'story' => 'timeline-vertical',
                'event' => 'card', 'gallery' => 'grid', 'gift' => 'cashless-modal',
                'rsvp' => 'default', 'guestbook' => 'default',
            },
            'sort_order' => $i,
            'enabled' => true,
        ]);
    }
});

it('renders preview without auth check on admin', function () {
    $this->actingAs($this->admin)
        ->get(route('invitations.preview', $this->invitation->slug))
        ->assertOk();
});

it('does not increment opens_count when previewed', function () {
    $guest = Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'opens_count' => 0,
        'token' => GuestToken::ensureUnique(),
    ]);

    $this->actingAs($this->admin)
        ->get(route('invitations.preview', $this->invitation->slug))
        ->assertOk();

    expect($guest->fresh()->opens_count)->toBe(0);
});

it('preview is gated by view policy for couples', function () {
    $couple = User::factory()->couple()->create();

    $this->actingAs($couple)
        ->get(route('invitations.preview', $this->invitation->slug))
        ->assertForbidden();
});

it('preview is allowed for invitation owner couple', function () {
    $couple = User::factory()->couple()->create();
    $this->invitation->update(['user_id' => $couple->id]);

    $this->actingAs($couple)
        ->get(route('invitations.preview', $this->invitation->slug))
        ->assertOk();
});

it('saving any tab increments previewKey', function () {
    $this->actingAs($this->admin);

    $component = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()]);
    $initial = $component->get('previewKey');

    $component->set('couple.brideName', 'X')
        ->set('couple.groomName', 'Y')
        ->call('saveCouple');

    expect($component->get('previewKey'))->toBe($initial + 1);
});

it('saving fires invitation-saved event', function () {
    $this->actingAs($this->admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->set('couple.brideName', 'X')
        ->set('couple.groomName', 'Y')
        ->call('saveCouple')
        ->assertDispatched('invitation-saved');
});
