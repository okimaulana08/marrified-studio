<?php

declare(strict_types=1);

use App\Livewire\Admin\Invitations\CredentialManager;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

it('renders empty state when no credentials linked', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);

    Livewire::test(CredentialManager::class, ['slug' => $invitation->slug])
        ->assertSee('Belum Ada Kredensial');
});

it('renders linked state when invitation already has user', function () {
    $user = User::factory()->couple()->create(['email' => 'existing@example.test']);
    $invitation = Invitation::factory()->create(['user_id' => $user->id]);

    Livewire::test(CredentialManager::class, ['slug' => $invitation->slug])
        ->assertSee('Akses Couple Aktif')
        ->assertSee('existing@example.test');
});

it('issues credentials and exposes plaintext password once', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);

    $component = Livewire::test(CredentialManager::class, ['slug' => $invitation->slug])
        ->set('email', 'new@example.test')
        ->call('issue')
        ->assertSet('flashType', 'success');

    $plaintext = $component->get('freshPlaintext');
    expect($plaintext)->toBeString()->and(strlen($plaintext))->toBe(12);

    expect($invitation->fresh()->user_id)->not->toBeNull();
});

it('rejects invalid email on issue', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);

    Livewire::test(CredentialManager::class, ['slug' => $invitation->slug])
        ->set('email', 'not-an-email')
        ->call('issue')
        ->assertHasErrors('email');
});

it('rejects issuing when invitation already has user', function () {
    $existing = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $existing->id]);

    Livewire::test(CredentialManager::class, ['slug' => $invitation->slug])
        ->set('email', 'new@example.test')
        ->call('issue')
        ->assertSet('flashType', 'error');
});

it('regenerates password and shows new plaintext', function () {
    $user = User::factory()->couple()->create(['email' => 'couple@example.test']);
    $invitation = Invitation::factory()->create(['user_id' => $user->id]);

    $component = Livewire::test(CredentialManager::class, ['slug' => $invitation->slug])
        ->call('regenerate')
        ->assertSet('flashType', 'success');

    $plaintext = $component->get('freshPlaintext');
    expect($plaintext)->toBeString()->and(strlen($plaintext))->toBe(12);
});

it('revokes access and unlinks the user', function () {
    $user = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $user->id]);

    Livewire::test(CredentialManager::class, ['slug' => $invitation->slug])
        ->call('revoke')
        ->assertSet('flashType', 'info');

    expect($invitation->fresh()->user_id)->toBeNull()
        ->and(User::query()->find($user->id))->toBeNull();
});

it('dismisses plaintext on demand', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);

    $component = Livewire::test(CredentialManager::class, ['slug' => $invitation->slug])
        ->set('email', 'x@y.test')
        ->call('issue');

    expect($component->get('freshPlaintext'))->not->toBeNull();

    $component->call('dismissPlaintext');

    expect($component->get('freshPlaintext'))->toBeNull();
});

it('couple user is forbidden from credentials page', function () {
    $couple = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($couple)
        ->get(route('admin.invitations.credentials', $invitation->slug))
        ->assertForbidden();
});

it('admin can navigate to credentials page', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.invitations.credentials', $invitation->slug))
        ->assertOk()
        ->assertSee($invitation->slug);
});
