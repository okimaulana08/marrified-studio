<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Event;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->invitation = Invitation::factory()->create();
});

it('starts with empty rows when no events exist', function () {
    $this->actingAs($this->admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->assertSet('events.rows', []);
});

it('loads existing events into rows ordered by sort_order', function () {
    Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'akad', 'name' => 'Akad', 'date' => '2027-06-01', 'time' => '08:00:00',
        'venue_name' => 'Masjid', 'sort_order' => 0,
    ]);
    Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'resepsi', 'name' => 'Resepsi', 'date' => '2027-06-01', 'time' => '11:00:00',
        'venue_name' => 'Hotel', 'sort_order' => 1,
    ]);

    $this->actingAs($this->admin);

    $component = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation]);

    expect($component->get('events.rows'))->toHaveCount(2)
        ->and($component->get('events.rows.0.name'))->toBe('Akad')
        ->and($component->get('events.rows.1.name'))->toBe('Resepsi');
});

it('adds and removes rows', function () {
    $this->actingAs($this->admin);

    $c = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation]);
    $c->call('addEventRow');
    expect($c->get('events.rows'))->toHaveCount(1);

    $c->call('addEventRow');
    expect($c->get('events.rows'))->toHaveCount(2);

    $c->call('removeEventRow', 0);
    expect($c->get('events.rows'))->toHaveCount(1);
});

it('moves rows up and down', function () {
    Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'akad', 'name' => 'A', 'date' => '2027-01-01', 'venue_name' => 'V', 'sort_order' => 0,
    ]);
    Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'resepsi', 'name' => 'B', 'date' => '2027-01-01', 'venue_name' => 'V', 'sort_order' => 1,
    ]);

    $this->actingAs($this->admin);

    $c = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh(['events'])]);
    expect($c->get('events.rows.0.name'))->toBe('A');

    $c->call('moveEventDown', 0);
    expect($c->get('events.rows.0.name'))->toBe('B')
        ->and($c->get('events.rows.1.name'))->toBe('A');
});

it('persists new events on save', function () {
    $this->actingAs($this->admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addEventRow')
        ->set('events.rows.0.type', 'akad')
        ->set('events.rows.0.name', 'Akad Nikah')
        ->set('events.rows.0.date', '2027-09-15')
        ->set('events.rows.0.time', '08:00')
        ->set('events.rows.0.venue_name', 'Masjid Al-Falah')
        ->call('saveEvents')
        ->assertSet('flashType', 'success');

    expect(Event::query()->where('invitation_id', $this->invitation->id)->count())->toBe(1);
    expect(Event::query()->where('invitation_id', $this->invitation->id)->first()->name)->toBe('Akad Nikah');
});

it('updates existing events and deletes removed ones', function () {
    $keep = Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'akad', 'name' => 'Original', 'date' => '2027-01-01', 'venue_name' => 'V1', 'sort_order' => 0,
    ]);
    $remove = Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'resepsi', 'name' => 'Drop me', 'date' => '2027-01-01', 'venue_name' => 'V2', 'sort_order' => 1,
    ]);

    $this->actingAs($this->admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh(['events'])])
        ->set('events.rows.0.name', 'Renamed Event')
        ->call('removeEventRow', 1)
        ->call('saveEvents')
        ->assertSet('flashType', 'success');

    expect(Event::query()->find($keep->id)->name)->toBe('Renamed Event')
        ->and(Event::query()->find($remove->id))->toBeNull();
});

it('rejects rows with missing required fields', function () {
    $this->actingAs($this->admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addEventRow')
        ->set('events.rows.0.name', '')
        ->set('events.rows.0.date', '')
        ->set('events.rows.0.venue_name', '')
        ->call('saveEvents')
        ->assertHasErrors([
            'events.rows.0.name',
            'events.rows.0.date',
            'events.rows.0.venue_name',
        ]);
});
