<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Guest;
use App\Models\GuestbookMessage;
use App\Models\Invitation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class GuestbookForm extends Component
{
    public Invitation $invitation;

    public ?Guest $guest = null;

    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('required|string|min:3|max:500')]
    public string $message = '';

    public bool $submitted = false;

    public function mount(Invitation $invitation, ?Guest $guest = null): void
    {
        $this->invitation = $invitation;
        $this->guest = $guest;
        if ($guest) {
            $this->name = $guest->name;
        }
    }

    public function submit(): void
    {
        $this->validate();

        GuestbookMessage::query()->create([
            'invitation_id' => $this->invitation->id,
            'guest_id' => $this->guest?->id,
            'name' => $this->name,
            'message' => $this->message,
            'is_visible' => true,
        ]);

        $this->submitted = true;
        $this->reset('message');
    }

    public function render(): View
    {
        return view('livewire.public.guestbook-form');
    }
}
