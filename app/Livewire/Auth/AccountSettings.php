<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Self-service account settings for the logged-in user. Currently:
 *   - Change password (verifies current first)
 *   - Email displayed read-only
 *   - 2FA placeholder (out of scope)
 */
final class AccountSettings extends Component
{
    #[Validate('required|string|current_password')]
    public string $currentPassword = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    #[Validate('required|string|min:8|same:password')]
    public string $passwordConfirmation = '';

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    /** Friendlier validation messages. */
    protected function messages(): array
    {
        return [
            'currentPassword.current_password' => 'Password saat ini tidak cocok.',
            'passwordConfirmation.same' => 'Konfirmasi password tidak sama.',
        ];
    }

    public function rules(): array
    {
        return [
            'currentPassword' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::min(8)],
            'passwordConfirmation' => ['required', 'string', 'same:password'],
        ];
    }

    public function changePassword(): void
    {
        $this->validate();

        $user = Auth::user();
        $user->password = Hash::make($this->password);
        $user->save();

        $this->reset(['currentPassword', 'password', 'passwordConfirmation']);
        $this->flash('Password berhasil diubah.', 'success');
    }

    private function flash(string $message, string $type): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function render(): View
    {
        return view('livewire.auth.account-settings', [
            'user' => Auth::user(),
        ]);
    }
}
