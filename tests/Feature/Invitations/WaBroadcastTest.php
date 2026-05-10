<?php

declare(strict_types=1);

use App\Livewire\Invitations\GuestsTab;
use App\Models\Couple;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
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
        'name' => 'Akad',
        'date' => '2026-09-15',
        'venue_name' => 'Masjid Al-Hidayah',
        'sort_order' => 1,
    ]);
});

it('loads default WA template on mount', function () {
    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])->assertSet('waTemplate', GuestsTab::DEFAULT_WA_TEMPLATE);
});

it('persists wa template to invitation row', function () {
    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->set('waTemplate', 'Halo {nama}, ke wedding {bride} & {groom}!')
        ->call('saveWaTemplate')
        ->assertSet('flashType', 'success');

    expect($this->invitation->fresh()->wa_broadcast_template)
        ->toBe('Halo {nama}, ke wedding {bride} & {groom}!');
});

it('reset returns template to default and persists', function () {
    $this->invitation->update(['wa_broadcast_template' => 'custom']);

    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->call('resetWaTemplate')
        ->assertSet('waTemplate', GuestsTab::DEFAULT_WA_TEMPLATE);

    expect($this->invitation->fresh()->wa_broadcast_template)
        ->toBe(GuestsTab::DEFAULT_WA_TEMPLATE);
});

it('renders WA link in row when guest has phone', function () {
    Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Budi',
        'phone' => '081234567890',
        'token' => 'AbCdEf1234',
    ]);

    $html = Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])->html();

    expect($html)->toContain('https://wa.me/6281234567890');
    // Resolved template should contain the substituted couple + link.
    expect($html)->toContain('Dewi');
    expect($html)->toContain('raka-dewi/AbCdEf1234');
});

it('does not render WA button when phone missing', function () {
    Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Tanpa Phone',
        'phone' => null,
        'token' => 'NoPhone001',
    ]);

    $html = Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])->html();

    // The row should not include a wa.me link
    expect($html)->not->toContain('wa.me');
    // But the row should still render (Copy is always available)
    expect($html)->toContain('Tanpa Phone');
});

it('exports csv with header row and a resolved row per guest', function () {
    Guest::factory()->create([
        'invitation_id' => $this->invitation->id,
        'name' => 'Ani Wijaya',
        'phone' => '081234567890',
        'token' => 'Tok0000001',
    ]);

    // Call exportCsv on a hydrated component directly: Livewire::test()->call()
    // does not surface a StreamedResponse via its own property bag.
    $component = new GuestsTab();
    $component->mount($this->invitation->id, true);

    /** @var \Symfony\Component\HttpFoundation\StreamedResponse $stream */
    $stream = $component->exportCsv();

    ob_start();
    $stream->sendContent();
    $body = ob_get_clean();

    expect($body)->toContain('name,phone,token_url,message');
    expect($body)->toContain('Ani Wijaya');
    expect($body)->toContain('6281234567890');
    expect($body)->toContain('raka-dewi/Tok0000001');
});
