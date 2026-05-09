<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\User;
use App\Services\Invitations\CoupleCredentialIssuer;

it('admin issues credentials, then couple logs in and lands on their editor', function () {
    // 1. Admin issues credentials.
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create([
        'slug' => 'sari-budi',
        'user_id' => null,
    ]);

    $this->actingAs($admin);
    $issuer = new CoupleCredentialIssuer;
    $result = $issuer->issue($invitation, 'sari-budi@couple.test');
    $plaintext = $result['plaintext_password'];

    auth()->logout();

    // 2. Couple logs in with the issued credentials.
    $this->post('/login', [
        'email' => 'sari-budi@couple.test',
        'password' => $plaintext,
    ])->assertRedirect(route('invitations.edit', 'sari-budi'));

    expect(auth()->id())->toBe($result['user']->id);
});

it('regenerated password works while old password no longer does', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create(['slug' => 'rene-leo', 'user_id' => null]);

    $this->actingAs($admin);
    $issuer = new CoupleCredentialIssuer;
    $first = $issuer->issue($invitation, 'rl@couple.test');
    $second = $issuer->regenerate($invitation->fresh());

    auth()->logout();

    // Old password rejected.
    $this->post('/login', [
        'email' => 'rl@couple.test',
        'password' => $first['plaintext_password'],
    ])->assertSessionHasErrors('email');

    // New password accepted.
    $this->post('/login', [
        'email' => 'rl@couple.test',
        'password' => $second['plaintext_password'],
    ])->assertRedirect(route('invitations.edit', 'rene-leo'));
});

it('revoked user cannot log in', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create(['user_id' => null]);

    $this->actingAs($admin);
    $issuer = new CoupleCredentialIssuer;
    $result = $issuer->issue($invitation, 'revoke@couple.test');
    $issuer->revoke($invitation->fresh());

    auth()->logout();

    $this->post('/login', [
        'email' => 'revoke@couple.test',
        'password' => $result['plaintext_password'],
    ])->assertSessionHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('dashboard redirects logged-in couple to their editor', function () {
    $couple = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create([
        'slug' => 'their-slug',
        'user_id' => $couple->id,
    ]);

    $this->actingAs($couple)
        ->get('/dashboard')
        ->assertRedirect(route('invitations.edit', 'their-slug'));
});

it('dashboard shows empty state when couple has no invitation', function () {
    $orphan = User::factory()->couple()->create();

    $this->actingAs($orphan)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('belum men-link invitation');
});
