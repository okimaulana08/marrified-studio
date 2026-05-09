<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

it('exposes islam field keys when religion is islam', function () {
    $invitation = Invitation::factory()->create([
        'religion_type' => 'islam',
        'religious_text' => null,
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->assertSet('religious.values.ayat', '')
        ->assertSet('religious.values.translation', '')
        ->assertSet('religious.values.source', '');
});

it('exposes verse field keys when religion is christian', function () {
    $invitation = Invitation::factory()->create([
        'religion_type' => 'christian',
        'religious_text' => null,
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->assertSet('religious.values.verse', '')
        ->assertSet('religious.values.translation', '')
        ->assertSet('religious.values.source', '');
});

it('hides all keys when religion is none', function () {
    $invitation = Invitation::factory()->create(['religion_type' => 'none']);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->assertSet('religious.values', []);
});

it('persists islam religious text to JSON column', function () {
    $invitation = Invitation::factory()->create(['religion_type' => 'islam']);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('religious.values.ayat', 'وَمِنْ آيَاتِهِ')
        ->set('religious.values.translation', 'Dan di antara tanda-tanda')
        ->set('religious.values.source', 'QS Ar-Rum: 21')
        ->call('saveReligious')
        ->assertSet('flashType', 'success');

    $stored = $invitation->fresh()->religious_text;
    expect($stored)
        ->toBeArray()
        ->ayat->toBe('وَمِنْ آيَاتِهِ')
        ->translation->toBe('Dan di antara tanda-tanda')
        ->source->toBe('QS Ar-Rum: 21');
});

it('drops empty fields and stores only non-blank ones', function () {
    $invitation = Invitation::factory()->create(['religion_type' => 'islam']);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('religious.values.ayat', 'short text')
        ->set('religious.values.translation', '')
        ->set('religious.values.source', '   ')
        ->call('saveReligious')
        ->assertSet('flashType', 'success');

    $stored = $invitation->fresh()->religious_text;
    expect($stored)->toBe(['ayat' => 'short text']);
});

it('clears religious_text when religion is none', function () {
    $invitation = Invitation::factory()->create([
        'religion_type' => 'none',
        'religious_text' => ['stale' => 'data'],
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->call('saveReligious');

    expect($invitation->fresh()->religious_text)->toBeNull();
});

it('resyncs keys after religion change via Basic save', function () {
    $invitation = Invitation::factory()->create([
        'religion_type' => 'islam',
        'religious_text' => null,
    ]);

    $component = Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('religious.values.ayat', 'A')
        ->set('religious.values.translation', 'shared field')
        ->set('basic.religionType', 'christian')
        ->call('save');

    // After religion changed: ayat is dropped (islam-only), verse appears empty
    // (christian-only), translation/source carry over since they're shared keys.
    expect($component->get('religious.values'))->not->toHaveKey('ayat');
    $component->assertSet('religious.values.verse', '')
        ->assertSet('religious.values.translation', 'shared field');
});
