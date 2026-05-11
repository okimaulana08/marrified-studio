<?php

declare(strict_types=1);

use App\Livewire\Invitations\GuestbookModeration;
use App\Models\GuestbookMessage;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
    $this->invitation = Invitation::factory()->create();
});

it('lists all guestbook messages by default', function () {
    GuestbookMessage::factory()->count(3)->create(['invitation_id' => $this->invitation->id]);

    Livewire::test(GuestbookModeration::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])->assertSee('Moderasi Buku Tamu');
});

it('toggles visibility on a message', function () {
    $msg = GuestbookMessage::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Spammy',
        'is_visible' => true,
    ]);

    Livewire::test(GuestbookModeration::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->call('toggleVisibility', $msg->id)
        ->assertSet('flashType', 'success');

    expect($msg->fresh()->is_visible)->toBeFalse();
});

it('filters visible-only when chip is selected', function () {
    GuestbookMessage::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Visible Person',
        'is_visible' => true,
    ]);
    GuestbookMessage::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Hidden Person',
        'is_visible' => false,
    ]);

    $html = Livewire::test(GuestbookModeration::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->set('filter', 'visible')
        ->html();

    expect($html)->toContain('Visible Person')
        ->and($html)->not->toContain('Hidden Person');
});

it('deletes a message via confirm modal', function () {
    $msg = GuestbookMessage::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Goner',
    ]);

    Livewire::test(GuestbookModeration::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->call('confirmDelete', $msg->id)
        ->assertSet('showDeleteModal', true)
        ->call('deleteMessage')
        ->assertSet('showDeleteModal', false);

    expect(GuestbookMessage::query()->find($msg->id))->toBeNull();
});

it('forbids a couple user from moderating someone elses invitation', function () {
    $stranger = User::factory()->couple()->create();
    $this->actingAs($stranger);

    $msg = GuestbookMessage::factory()->create(['invitation_id' => $this->invitation->id]);

    Livewire::test(GuestbookModeration::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => false,
    ])
        ->call('toggleVisibility', $msg->id)
        ->assertForbidden();
});
