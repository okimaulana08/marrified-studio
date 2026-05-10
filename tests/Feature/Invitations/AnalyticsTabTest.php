<?php

declare(strict_types=1);

use App\Livewire\Invitations\AnalyticsTab;
use App\Models\Couple;
use App\Models\Event;
use App\Models\Guest;
use App\Models\GuestbookMessage;
use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
    $this->invitation = Invitation::factory()->create(['slug' => 'raka-dewi']);
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_name' => 'Dewi',
        'groom_name' => 'Raka',
    ]);
    Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'date' => '2026-09-15',
        'venue_name' => 'X',
        'sort_order' => 1,
    ]);
});

it('renders empty-state friendly when no data exists', function () {
    Livewire::test(AnalyticsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->assertSee('Total Opens')
        ->assertSee('Belum ada RSVP masuk');
});

it('counts total opens and unique openers correctly', function () {
    Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'opens_count' => 5,
        'first_opened_at' => now()->subDays(2),
    ]);
    Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'opens_count' => 3,
        'first_opened_at' => now()->subDay(),
    ]);
    Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'opens_count' => 0,
    ]);

    Livewire::test(AnalyticsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->assertSee('8')           // total opens 5+3
        ->assertSeeInOrder(['Sudah Buka', '2', '/3'])   // unique opened markup split by span
        ->assertSee('67%');        // percentage rounded
});

it('breaks down RSVP attendance counts', function () {
    Rsvp::factory()->create(['invitation_id' => $this->invitation->id, 'attendance' => 'attending', 'party_size' => 2]);
    Rsvp::factory()->create(['invitation_id' => $this->invitation->id, 'attendance' => 'attending', 'party_size' => 1]);
    Rsvp::factory()->create(['invitation_id' => $this->invitation->id, 'attendance' => 'not_attending', 'party_size' => 1]);
    Rsvp::factory()->create(['invitation_id' => $this->invitation->id, 'attendance' => 'maybe', 'party_size' => 1]);

    $html = Livewire::test(AnalyticsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])->html();

    // Attending count card shows 2, headcount sum is 3
    expect($html)->toContain('~3 headcount');
});

it('lists guests who have not opened yet with WA reminder link', function () {
    Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Belum Buka',
        'phone' => '081234567890',
        'token' => 'TokenABC01',
        'opens_count' => 0,
    ]);

    $html = Livewire::test(AnalyticsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])->html();

    expect($html)->toContain('Belum Buka')
        ->and($html)->toContain('https://wa.me/6281234567890');
});

it('counts visible guestbook messages only', function () {
    GuestbookMessage::factory()->create([
        'invitation_id' => $this->invitation->id,
        'is_visible' => true,
    ]);
    GuestbookMessage::factory()->create([
        'invitation_id' => $this->invitation->id,
        'is_visible' => true,
    ]);
    GuestbookMessage::factory()->create([
        'invitation_id' => $this->invitation->id,
        'is_visible' => false,
    ]);

    Livewire::test(AnalyticsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])->assertSeeInOrder(['Ucapan', '2']);
});

it('forbids couple from accessing analytics of a different invitation', function () {
    $other = User::factory()->couple()->create();
    $this->actingAs($other);

    Livewire::test(AnalyticsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => false,
    ])->assertForbidden();
});
