<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\User;

it('admin sees the invitations index', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.invitations.index'))
        ->assertOk()
        ->assertSee('Semua Invitation');
});

it('couple is forbidden from invitations index', function () {
    $couple = User::factory()->couple()->create();

    $this->actingAs($couple)
        ->get(route('admin.invitations.index'))
        ->assertForbidden();
});

it('admin can delete an invitation', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.invitations.destroy', $invitation->slug))
        ->assertRedirect(route('admin.invitations.index'));

    expect(Invitation::query()->find($invitation->id))->toBeNull();
});

it('couple cannot delete an invitation even their own', function () {
    $couple = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $couple->id]);

    $this->actingAs($couple)
        ->delete(route('admin.invitations.destroy', $invitation->slug))
        ->assertForbidden();

    expect(Invitation::query()->find($invitation->id))->not->toBeNull();
});
