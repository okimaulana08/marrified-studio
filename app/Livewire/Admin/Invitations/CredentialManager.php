<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Invitations;

use App\Models\Invitation;
use App\Models\User;
use App\Services\Invitations\CoupleCredentialIssuer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

/**
 * Admin-only credential manager for one invitation. States:
 *   1. No user linked → input email, "Issue".
 *   2. Just issued / regenerated → show email + plaintext password ONCE
 *      with copy button. Plaintext only lives in component memory; reload
 *      the page or close the modal and it's gone.
 *   3. User linked but no fresh plaintext → show email + Regenerate + Revoke.
 *
 * Authorization: route is gated by `auth + role:admin` middleware (admin only).
 * Couples never reach this component.
 */
final class CredentialManager extends Component
{
    public string $slug = '';

    #[Validate('required|email|max:200')]
    public string $email = '';

    /** Plaintext password from the most recent issue/regenerate. Cleared on dismiss. */
    public ?string $freshPlaintext = null;

    public ?string $flashMessage = null;

    public ?string $flashType = null;

    public function mount(string $slug): void
    {
        $invitation = Invitation::query()->where('slug', $slug)->firstOrFail();
        $this->slug = $invitation->slug;
    }

    public function issue(CoupleCredentialIssuer $issuer): void
    {
        $this->validate();
        $invitation = $this->loadInvitation();

        try {
            $result = $issuer->issue($invitation, $this->email);

            $this->freshPlaintext = $result['plaintext_password'];
            $this->email = $result['user']->email;
            $this->flash('Kredensial berhasil di-issue. Salin password sekarang — hanya tampil sekali.', 'success');
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function regenerate(CoupleCredentialIssuer $issuer): void
    {
        $invitation = $this->loadInvitation();

        try {
            $result = $issuer->regenerate($invitation);

            $this->freshPlaintext = $result['plaintext_password'];
            $this->email = $result['user']->email;
            $this->flash('Password baru di-generate. Salin sekarang — hanya tampil sekali.', 'success');
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function revoke(CoupleCredentialIssuer $issuer): void
    {
        $invitation = $this->loadInvitation();

        try {
            $issuer->revoke($invitation);

            $this->freshPlaintext = null;
            $this->email = '';
            $this->flash('Akses couple dicabut. User dihapus.', 'info');
        } catch (RuntimeException $e) {
            $this->flash($e->getMessage(), 'error');
        }
    }

    public function dismissPlaintext(): void
    {
        $this->freshPlaintext = null;
    }

    private function loadInvitation(): Invitation
    {
        return Invitation::query()->where('slug', $this->slug)->firstOrFail();
    }

    private function flash(string $message, string $type): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function render(): View
    {
        $invitation = Invitation::query()
            ->where('slug', $this->slug)
            ->with('couple')
            ->firstOrFail();

        $linkedUser = $invitation->user_id !== null
            ? User::query()->find($invitation->user_id)
            : null;

        return view('livewire.admin.invitations.credential-manager', [
            'invitation' => $invitation,
            'linkedUser' => $linkedUser,
            'loginUrl' => route('login'),
        ]);
    }
}
