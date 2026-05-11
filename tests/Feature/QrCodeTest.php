<?php

declare(strict_types=1);

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
    $this->invitation = Invitation::factory()->create(['slug' => 'raka-dewi']);
});

it('returns a PNG for the main invitation QR', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('invitations.qr', 'raka-dewi'));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('image/png');
});

it('returns a PNG for a per-guest QR', function () {
    $this->actingAs($this->admin);
    $guest = Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'token' => 'AbCdEf1234',
    ]);

    $response = $this->get(route('invitations.guests.qr', ['slug' => 'raka-dewi', 'guest' => $guest->id]));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('image/png');
});

it('caches the QR file under storage/app/public/qr', function () {
    $this->actingAs($this->admin);

    $this->get(route('invitations.qr', 'raka-dewi'))->assertOk();

    $files = Storage::disk('public')->files('qr');
    expect($files)->not->toBeEmpty();
    expect($files[0])->toEndWith('.png');
});

it('forbids couple from QR of a different invitation', function () {
    $stranger = User::factory()->couple()->create();
    $this->actingAs($stranger);

    $this->get(route('invitations.qr', 'raka-dewi'))->assertForbidden();
});

it('returns 404 for guest belonging to another invitation', function () {
    $this->actingAs($this->admin);
    $otherInvitation = Invitation::factory()->create();
    $guest = Guest::factory()->create(['invitation_id' => $otherInvitation->id]);

    $this->get(route('invitations.guests.qr', ['slug' => 'raka-dewi', 'guest' => $guest->id]))
        ->assertNotFound();
});
