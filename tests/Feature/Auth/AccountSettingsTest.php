<?php

declare(strict_types=1);

use App\Livewire\Auth\AccountSettings;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->couple()->create([
        'password' => Hash::make('password-lama'),
    ]);
    $this->actingAs($this->user);
});

it('renders the account settings page for logged-in user', function () {
    $this->get(route('account'))
        ->assertOk()
        ->assertSee('Pengaturan Akun')
        ->assertSee($this->user->email);
});

it('redirects unauthenticated visitors to login', function () {
    auth()->logout();
    $this->get(route('account'))->assertRedirect(route('login'));
});

it('changes password when current password is correct', function () {
    Livewire::test(AccountSettings::class)
        ->set('currentPassword', 'password-lama')
        ->set('password', 'password-baru-123')
        ->set('passwordConfirmation', 'password-baru-123')
        ->call('changePassword')
        ->assertSet('flashType', 'success');

    expect(Hash::check('password-baru-123', $this->user->fresh()->password))->toBeTrue();
});

it('rejects when current password is wrong', function () {
    Livewire::test(AccountSettings::class)
        ->set('currentPassword', 'salah')
        ->set('password', 'password-baru-123')
        ->set('passwordConfirmation', 'password-baru-123')
        ->call('changePassword')
        ->assertHasErrors(['currentPassword']);

    // Password should not have changed
    expect(Hash::check('password-lama', $this->user->fresh()->password))->toBeTrue();
});

it('rejects when confirmation does not match', function () {
    Livewire::test(AccountSettings::class)
        ->set('currentPassword', 'password-lama')
        ->set('password', 'password-baru-123')
        ->set('passwordConfirmation', 'beda-nih')
        ->call('changePassword')
        ->assertHasErrors(['passwordConfirmation']);
});

it('rejects passwords shorter than 8 characters', function () {
    Livewire::test(AccountSettings::class)
        ->set('currentPassword', 'password-lama')
        ->set('password', 'short')
        ->set('passwordConfirmation', 'short')
        ->call('changePassword')
        ->assertHasErrors(['password']);
});

it('clears form fields after successful change', function () {
    Livewire::test(AccountSettings::class)
        ->set('currentPassword', 'password-lama')
        ->set('password', 'password-baru-123')
        ->set('passwordConfirmation', 'password-baru-123')
        ->call('changePassword')
        ->assertSet('currentPassword', '')
        ->assertSet('password', '')
        ->assertSet('passwordConfirmation', '');
});
