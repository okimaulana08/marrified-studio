<?php

declare(strict_types=1);

use App\Models\Couple;
use App\Models\Event;
use App\Models\Invitation;
use App\Models\Section;

beforeEach(function () {
    $this->invitation = Invitation::factory()->create(['slug' => 'raka-dewi']);
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_name' => 'Dewi Lestari',
        'bride_nickname' => 'Dewi',
        'groom_name' => 'Raka Pratama',
        'groom_nickname' => 'Raka',
    ]);
    Event::factory()->create([
        'invitation_id' => $this->invitation->id,
        'date' => '2026-09-15',
        'venue_name' => 'Masjid Al-Hidayah',
        'sort_order' => 1,
    ]);
    // Need at least cover section enabled to render
    Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'cover',
        'variant' => 'minimal',
        'enabled' => true,
        'sort_order' => 1,
    ]);
});

it('renders open graph title with bride & groom names', function () {
    $response = $this->get('/raka-dewi');
    $response->assertOk();
    $response->assertSee('<meta property="og:title" content="Dewi &amp; Raka — Undangan Pernikahan">', false);
});

it('renders og description with event date and venue', function () {
    $response = $this->get('/raka-dewi');
    $response->assertSee('og:description', false);
    $response->assertSee('Masjid Al-Hidayah', false);
});

it('renders absolute og:url for the current page', function () {
    $response = $this->get('/raka-dewi');
    $html = $response->getContent();
    expect($html)->toMatch('#<meta property="og:url" content="https?://[^"]+/raka-dewi[^"]*">#');
});

it('renders twitter card meta when bride or groom photo is available', function () {
    // Couple without photo → still has theme preview fallback OR summary card.
    $response = $this->get('/raka-dewi');
    $response->assertSee('twitter:card', false);
    $response->assertSee('twitter:title', false);
});

it('renders all OG core tags as present', function () {
    $response = $this->get('/raka-dewi');
    $html = $response->getContent();
    expect($html)
        ->toContain('property="og:type"')
        ->toContain('property="og:title"')
        ->toContain('property="og:description"')
        ->toContain('property="og:url"');
});
