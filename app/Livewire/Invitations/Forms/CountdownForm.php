<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Models\Invitation;
use App\Models\Section;
use Livewire\Attributes\Validate;
use Livewire\Form;

/**
 * Countdown section content. Stored as JSON in Section.content (type='countdown'):
 *   { title, message }
 *
 * Target date is sourced from the first event's date — couples don't pick it
 * separately to keep the data single-sourced. Variants render the live timer.
 */
final class CountdownForm extends Form
{
    #[Validate('nullable|string|max:80')]
    public string $title = 'Hitung Mundur';

    #[Validate('nullable|string|max:300')]
    public string $message = '';

    public function fillFromSection(?Section $section): void
    {
        $content = (array) ($section?->content ?? []);

        $this->title = (string) ($content['title'] ?? 'Hitung Mundur');
        $this->message = (string) ($content['message'] ?? '');
    }

    public function persist(Invitation $invitation): void
    {
        $section = Section::query()
            ->where('invitation_id', $invitation->id)
            ->where('type', 'countdown')
            ->first();

        if ($section === null) {
            return;
        }

        $section->content = [
            'title' => $this->title !== '' ? $this->title : 'Hitung Mundur',
            'message' => $this->message,
        ];
        $section->save();
    }
}
