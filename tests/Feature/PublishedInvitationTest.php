<?php

declare(strict_types=1);

use App\Models\Couple;
use App\Models\Event;
use App\Models\GiftAccount;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Section;

beforeEach(function () {
    $this->invitation = Invitation::factory()->create(['slug' => 'raka-dewi', 'theme_slug' => 'watercolor-lush']);
    Couple::factory()->create(['invitation_id' => $this->invitation->id]);
    Event::factory()->akad()->create(['invitation_id' => $this->invitation->id]);
    Event::factory()->resepsi()->create(['invitation_id' => $this->invitation->id]);
    GiftAccount::factory()->create(['invitation_id' => $this->invitation->id]);

    foreach (['cover', 'quotes', 'couple', 'event', 'gallery', 'gift', 'rsvp', 'guestbook'] as $i => $type) {
        Section::factory()->create([
            'invitation_id' => $this->invitation->id,
            'type' => $type,
            'variant' => 'default',
            'sort_order' => $i,
            'enabled' => true,
        ]);
    }
});

it('renders the invitation public page without a guest token', function () {
    $response = $this->get('/raka-dewi');

    $response->assertOk()
        ->assertSee('The Wedding of', false)
        ->assertSee('Mempelai', false)
        ->assertSee('Acara', false);
    expect($response->getContent())->not->toContain('Kepada Yth.');
});

it('renders guest greeting when token matches', function () {
    $guest = Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Pak Budi Hartono',
        'relation' => 'Bapak',
    ]);

    $response = $this->get('/raka-dewi/'.$guest->token);

    $response->assertOk()
        ->assertSee('Kepada Yth.', false)
        ->assertSee('Pak Budi Hartono');
});

it('returns 404 when slug does not exist', function () {
    $this->get('/does-not-exist')->assertStatus(404);
});

it('renders generic cover when token does not match any guest', function () {
    Guest::factory()->create(['invitation_id' => $this->invitation->id]);

    $response = $this->get('/raka-dewi/zzzzzzzzzz');
    $response->assertOk();
    expect($response->getContent())->not->toContain('Kepada Yth.');
});

it('increments opens_count when guest opens via token', function () {
    $guest = Guest::factory()->create(['invitation_id' => $this->invitation->id, 'opens_count' => 0]);

    $this->get('/raka-dewi/'.$guest->token)->assertOk();
    $this->get('/raka-dewi/'.$guest->token)->assertOk();

    expect($guest->fresh()->opens_count)->toBe(2)
        ->and($guest->fresh()->first_opened_at)->not->toBeNull()
        ->and($guest->fresh()->last_opened_at)->not->toBeNull();
});
