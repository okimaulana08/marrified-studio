<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Rsvp;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class RsvpForm extends Component
{
    public Invitation $invitation;

    public ?Guest $guest = null;

    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('nullable|string|max:32')]
    public string $phone = '';

    #[Validate('required|in:attending,not_attending,maybe')]
    public string $attendance = 'attending';

    #[Validate('required|integer|min:1|max:10')]
    public int $partySize = 1;

    #[Validate('nullable|string|max:500')]
    public string $note = '';

    public bool $submitted = false;

    public function mount(Invitation $invitation, ?Guest $guest = null): void
    {
        $this->invitation = $invitation;
        $this->guest = $guest;
        if ($guest) {
            $this->name = $guest->name;
            $this->phone = $guest->phone ?? '';
        }
    }

    public function submit(): void
    {
        $this->validate();

        Rsvp::query()->create([
            'invitation_id' => $this->invitation->id,
            'guest_id' => $this->guest?->id,
            'name' => $this->name,
            'phone' => $this->phone !== '' ? $this->phone : null,
            'attendance' => $this->attendance,
            'party_size' => $this->partySize,
            'note' => $this->note !== '' ? $this->note : null,
        ]);

        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.public.rsvp-form');
    }
}
