<?php

declare(strict_types=1);

use App\Models\Couple;
use App\Models\Event;
use App\Models\GiftAccount;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Section;
use App\Services\Invitations\CompletionAuditor;

beforeEach(function () {
    $this->invitation = Invitation::factory()->create();
    $this->auditor = app(CompletionAuditor::class);
});

it('returns 0% for a blank invitation', function () {
    // Disable all optional sections so only the 4 base items remain (couple names,
    // couple photos, events, guests). All undone → 0%.
    Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'gift', 'enabled' => false]);
    Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'gallery', 'enabled' => false]);
    Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'story', 'enabled' => false]);
    Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'thanks', 'enabled' => false]);

    $result = $this->auditor->audit($this->invitation->fresh());

    expect($result['percent'])->toBe(0)
        ->and($result['todos'])->toHaveCount(4); // names, photos, events, guests
});

it('returns 100% when every required item is satisfied', function () {
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_name' => 'Anin',
        'groom_name' => 'Farhan',
        'bride_photo_path' => "{$this->invitation->id}/couple/bride.jpg",
        'groom_photo_path' => "{$this->invitation->id}/couple/groom.jpg",
    ]);
    Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'date' => '2026-09-15',
        'venue_name' => 'Masjid Al-Hidayah',
    ]);
    Guest::factory()->create(['invitation_id' => $this->invitation->id]);

    // All optional sections disabled so weights only come from the 4 required items.
    Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'gift', 'enabled' => false]);
    Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'gallery', 'enabled' => false]);
    Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'story', 'enabled' => false]);
    Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'thanks', 'enabled' => false]);

    $result = $this->auditor->audit($this->invitation->fresh());

    expect($result['percent'])->toBe(100)
        ->and($result['todos'])->toBeEmpty();
});

it('flags missing couple photos but still scores names done', function () {
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_name' => 'Anin',
        'groom_name' => 'Farhan',
        'bride_photo_path' => null,
        'groom_photo_path' => null,
    ]);

    $result = $this->auditor->audit($this->invitation->fresh());

    $items = $result['items'];
    expect($items['couple_names']['done'])->toBeTrue()
        ->and($items['couple_photos']['done'])->toBeFalse();
});

it('skips story todo when story section is disabled', function () {
    Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'story', 'enabled' => false]);

    $result = $this->auditor->audit($this->invitation->fresh());

    expect($result['items'])->not->toHaveKey('story');
});

it('includes story todo when story section is enabled but empty', function () {
    Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'story',
        'enabled' => true,
        'content' => ['entries' => []],
    ]);

    $result = $this->auditor->audit($this->invitation->fresh());

    expect($result['items']['story']['done'])->toBeFalse();
});

it('marks gift done when at least one gift account exists', function () {
    Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'gift',
        'enabled' => true,
    ]);
    GiftAccount::factory()->create(['invitation_id' => $this->invitation->id]);

    $result = $this->auditor->audit($this->invitation->fresh());

    expect($result['items']['gift']['done'])->toBeTrue();
});
