<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\GiftAccount;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    $this->invitation = Invitation::factory()->create();
});

it('starts with empty rows when no gift accounts exist', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->assertSet('gift.rows', []);
});

it('loads existing accounts ordered by sort_order', function () {
    GiftAccount::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'bank', 'bank_name' => 'BCA', 'account_number' => '111', 'account_name' => 'A',
        'sort_order' => 0,
    ]);
    GiftAccount::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'ewallet', 'bank_name' => 'GoPay', 'account_number' => '0812', 'account_name' => 'B',
        'sort_order' => 1,
    ]);

    $component = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh(['giftAccounts'])]);

    expect($component->get('gift.rows'))->toHaveCount(2)
        ->and($component->get('gift.rows.0.bank_name'))->toBe('BCA')
        ->and($component->get('gift.rows.1.bank_name'))->toBe('GoPay');
});

it('adds, persists, and saves new accounts', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addGiftRow')
        ->set('gift.rows.0.type', 'bank')
        ->set('gift.rows.0.bank_name', 'Mandiri')
        ->set('gift.rows.0.account_number', '987654321')
        ->set('gift.rows.0.account_name', 'Sari Dewi')
        ->call('saveGift')
        ->assertSet('flashType', 'success');

    expect(GiftAccount::query()->where('invitation_id', $this->invitation->id)->count())->toBe(1);
});

it('updates existing and removes dropped accounts', function () {
    $keep = GiftAccount::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'bank', 'bank_name' => 'Old Name', 'account_number' => '1', 'account_name' => 'X',
        'sort_order' => 0,
    ]);
    $drop = GiftAccount::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'bank', 'bank_name' => 'Drop me', 'account_number' => '2', 'account_name' => 'Y',
        'sort_order' => 1,
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh(['giftAccounts'])])
        ->set('gift.rows.0.bank_name', 'Updated Name')
        ->call('removeGiftRow', 1)
        ->call('saveGift');

    expect($keep->fresh()->bank_name)->toBe('Updated Name')
        ->and(GiftAccount::query()->find($drop->id))->toBeNull();
});

it('rejects invalid type enum', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->call('addGiftRow')
        ->set('gift.rows.0.type', 'cryptocurrency')
        ->set('gift.rows.0.bank_name', 'X')
        ->set('gift.rows.0.account_number', '1')
        ->set('gift.rows.0.account_name', 'Y')
        ->call('saveGift')
        ->assertHasErrors(['gift.rows.0.type']);
});

it('reorders gift accounts via moveDown', function () {
    GiftAccount::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'bank', 'bank_name' => 'A', 'account_number' => '1', 'account_name' => 'X', 'sort_order' => 0,
    ]);
    GiftAccount::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'bank', 'bank_name' => 'B', 'account_number' => '2', 'account_name' => 'Y', 'sort_order' => 1,
    ]);

    $c = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh(['giftAccounts'])]);
    expect($c->get('gift.rows.0.bank_name'))->toBe('A');

    $c->call('moveGiftDown', 0);
    expect($c->get('gift.rows.0.bank_name'))->toBe('B')
        ->and($c->get('gift.rows.1.bank_name'))->toBe('A');
});
