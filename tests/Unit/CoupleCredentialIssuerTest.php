<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Couple;
use App\Models\Invitation;
use App\Models\User;
use App\Services\Invitations\CoupleCredentialIssuer;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->issuer = new CoupleCredentialIssuer;
});

it('issues credentials for an invitation with no linked user', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);

    $result = $this->issuer->issue($invitation, 'couple@example.test');

    expect($result['user'])->toBeInstanceOf(User::class)
        ->and($result['user']->email)->toBe('couple@example.test')
        ->and($result['user']->role)->toBe(UserRole::Couple)
        ->and($result['plaintext_password'])->toHaveLength(12);

    expect(Hash::check($result['plaintext_password'], $result['user']->password))->toBeTrue()
        ->and($invitation->fresh()->user_id)->toBe($result['user']->id);
});

it('uses couple nicknames as the user name when available', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);
    Couple::factory()->create([
        'invitation_id' => $invitation->id,
        'bride_nickname' => 'Sari',
        'groom_nickname' => 'Budi',
    ]);

    $result = $this->issuer->issue($invitation->fresh(), 'cd@example.test');

    expect($result['user']->name)->toBe('Sari & Budi');
});

it('falls back to slug when no couple is set', function () {
    $invitation = Invitation::factory()->create(['slug' => 'no-couple', 'user_id' => null]);

    $result = $this->issuer->issue($invitation, 'no-couple@example.test');

    expect($result['user']->name)->toBe('no-couple');
});

it('rejects empty or invalid email', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);

    expect(fn () => $this->issuer->issue($invitation, ''))
        ->toThrow(RuntimeException::class, 'wajib');

    expect(fn () => $this->issuer->issue($invitation, 'not-an-email'))
        ->toThrow(RuntimeException::class, 'tidak valid');
});

it('rejects re-issuing when invitation already has credentials', function () {
    $existing = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $existing->id]);

    expect(fn () => $this->issuer->issue($invitation, 'new@example.test'))
        ->toThrow(RuntimeException::class, 'sudah punya kredensial');
});

it('rejects email already used by another user', function () {
    User::factory()->create(['email' => 'taken@example.test']);
    $invitation = Invitation::factory()->create(['user_id' => null]);

    expect(fn () => $this->issuer->issue($invitation, 'taken@example.test'))
        ->toThrow(RuntimeException::class, 'sudah dipakai');
});

it('regenerates password for a linked user', function () {
    $user = User::factory()->couple()->create(['password' => Hash::make('old-password')]);
    $invitation = Invitation::factory()->create(['user_id' => $user->id]);

    $result = $this->issuer->regenerate($invitation);

    expect($result['plaintext_password'])->toHaveLength(12)
        ->and($result['user']->id)->toBe($user->id);

    expect(Hash::check($result['plaintext_password'], $result['user']->password))->toBeTrue()
        ->and(Hash::check('old-password', $result['user']->password))->toBeFalse();
});

it('rejects regenerate when no credentials exist', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);

    expect(fn () => $this->issuer->regenerate($invitation))
        ->toThrow(RuntimeException::class, 'belum punya kredensial');
});

it('revokes by deleting the linked user (cascade nulls invitation user_id)', function () {
    $user = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $user->id]);

    $this->issuer->revoke($invitation);

    expect(User::query()->find($user->id))->toBeNull()
        ->and($invitation->fresh()->user_id)->toBeNull();
});

it('rejects revoke when no credentials exist', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);

    expect(fn () => $this->issuer->revoke($invitation))
        ->toThrow(RuntimeException::class, 'belum punya kredensial');
});
