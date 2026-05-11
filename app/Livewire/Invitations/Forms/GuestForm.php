<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Enums\GuestGroup;
use App\Models\Guest;
use Livewire\Attributes\Validate;
use Livewire\Form;

/**
 * Single guest, used both for inline add and edit-modal flows.
 * Token is auto-generated on insert via GuestToken::ensureUnique() — never
 * exposed in the form so it can't drift.
 */
final class GuestForm extends Form
{
    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('nullable|string|max:60')]
    public string $relation = '';

    #[Validate('nullable|string|max:40|in:family,friend,coworker,vendor,other')]
    public string $group = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    public function fillFromGuest(Guest $guest): void
    {
        $this->name = $guest->name;
        $this->relation = (string) $guest->relation;
        $this->group = (string) ($guest->group ?? '');
        $this->phone = (string) $guest->phone;
    }

    /**
     * @return array{name: string, relation: string, group: ?string, phone: ?string}
     */
    public function toAttributes(): array
    {
        return [
            'name' => trim($this->name),
            // Default 'Tamu' when blank to satisfy NOT NULL constraint AND match
            // the CSV importer's behavior so the two paths stay consistent.
            'relation' => $this->relation !== '' ? trim($this->relation) : 'Tamu',
            'group' => $this->group !== '' ? $this->group : null,
            'phone' => $this->phone !== '' ? trim($this->phone) : null,
        ];
    }

    public function blank(): void
    {
        $this->name = '';
        $this->relation = '';
        $this->group = '';
        $this->phone = '';
        $this->resetErrorBag();
    }
}
