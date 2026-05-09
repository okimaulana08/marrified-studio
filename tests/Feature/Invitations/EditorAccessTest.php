<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\User;

it('redirects guest to login when accessing editor', function () {
    $invitation = Invitation::factory()->create();

    $this->get(route('invitations.edit', $invitation->slug))
        ->assertRedirect(route('login'));
});

it('allows admin to access any invitation editor', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin)
        ->get(route('invitations.edit', $invitation->slug))
        ->assertOk()
        ->assertSee($invitation->slug);
});

it('allows couple to access their own invitation editor', function () {
    $couple = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $couple->id]);

    $this->actingAs($couple)
        ->get(route('invitations.edit', $invitation->slug))
        ->assertOk()
        ->assertSee($invitation->slug);
});

it('forbids couple from accessing another invitation', function () {
    $couple = User::factory()->couple()->create();
    $stranger = Invitation::factory()->create(['user_id' => User::factory()->couple()->create()->id]);

    $this->actingAs($couple)
        ->get(route('invitations.edit', $stranger->slug))
        ->assertForbidden();
});

it('returns 404 for non-existent invitation', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('invitations.edit', 'no-such-invitation'))
        ->assertNotFound();
});

it('preview is gated by view policy', function () {
    $couple = User::factory()->couple()->create();
    $other = Invitation::factory()->create(['user_id' => User::factory()->couple()->create()->id]);

    $this->actingAs($couple)
        ->get(route('invitations.preview', $other->slug))
        ->assertForbidden();
});
