<?php

declare(strict_types=1);

use App\Livewire\Admin\Invitations\InvitationList;
use App\Models\Couple;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

it('renders empty state when no invitations exist', function () {
    Livewire::test(InvitationList::class)
        ->assertSee('Belum ada invitation');
});

it('lists invitations with couple names', function () {
    $inv = Invitation::factory()->create(['slug' => 'budi-sari']);
    Couple::factory()->create([
        'invitation_id' => $inv->id,
        'bride_name' => 'Sari Dewi',
        'groom_name' => 'Budi Santoso',
    ]);

    Livewire::test(InvitationList::class)
        ->assertSee('budi-sari')
        ->assertSee('Sari Dewi')
        ->assertSee('Budi Santoso');
});

it('filters by slug or couple name', function () {
    $a = Invitation::factory()->create(['slug' => 'foo-bar']);
    Couple::factory()->create(['invitation_id' => $a->id, 'bride_name' => 'Aaa', 'groom_name' => 'Bbb']);
    $b = Invitation::factory()->create(['slug' => 'baz-qux']);
    Couple::factory()->create(['invitation_id' => $b->id, 'bride_name' => 'Ccc', 'groom_name' => 'Ddd']);

    Livewire::test(InvitationList::class)
        ->set('search', 'baz')
        ->assertSee('baz-qux')
        ->assertDontSee('foo-bar');

    Livewire::test(InvitationList::class)
        ->set('search', 'Aaa')
        ->assertSee('foo-bar')
        ->assertDontSee('baz-qux');
});

it('opens delete modal and removes the invitation on confirm', function () {
    $inv = Invitation::factory()->create(['slug' => 'kill-me']);

    Livewire::test(InvitationList::class)
        ->call('confirmDelete', $inv->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteTargetSlug', 'kill-me')
        ->call('deleteInvitation')
        ->assertSet('showDeleteModal', false);

    expect(Invitation::query()->find($inv->id))->toBeNull();
});

it('forbids couple from interacting with the list component', function () {
    $couple = User::factory()->couple()->create();
    $inv = Invitation::factory()->create();

    $this->actingAs($couple);

    Livewire::test(InvitationList::class)
        ->call('confirmDelete', $inv->id)
        ->assertForbidden();
});
