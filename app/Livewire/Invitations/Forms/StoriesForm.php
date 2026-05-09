<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Models\Invitation;
use App\Models\Section;
use Livewire\Form;

/**
 * Love Story / Timeline entries for an invitation.
 *
 * Stored as JSON in `Section.content['entries']` (Section type='story'):
 *   [{ year: '2020', title: 'Pertama bertemu', description: '...', photo_path: '12/story/01J...jpg' }]
 *
 * Photos are uploaded by the parent component (InvitationEditor) which holds
 * a TemporaryUploadedFile array keyed by row index. Saving the form then
 * delegates photo persistence to the parent and only flushes the rows JSON.
 */
final class StoriesForm extends Form
{
    /**
     * @var array<int, array{
     *     year: string,
     *     title: string,
     *     description: string,
     *     photo_path: ?string,
     * }>
     */
    public array $rows = [];

    public function rules(): array
    {
        return [
            'rows.*.year' => 'required|string|max:20',
            'rows.*.title' => 'required|string|max:120',
            'rows.*.description' => 'nullable|string|max:1000',
        ];
    }

    public function fillFromSection(?Section $section): void
    {
        $entries = (array) ($section?->content['entries'] ?? []);

        $this->rows = array_values(array_map(fn (array $e) => [
            'year' => (string) ($e['year'] ?? ''),
            'title' => (string) ($e['title'] ?? ''),
            'description' => (string) ($e['description'] ?? ''),
            'photo_path' => isset($e['photo_path']) && $e['photo_path'] !== '' ? (string) $e['photo_path'] : null,
        ], $entries));
    }

    public function addRow(): void
    {
        $this->rows[] = [
            'year' => '',
            'title' => '',
            'description' => '',
            'photo_path' => null,
        ];
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function moveUp(int $index): void
    {
        if ($index <= 0 || ! isset($this->rows[$index])) {
            return;
        }
        [$this->rows[$index - 1], $this->rows[$index]] = [$this->rows[$index], $this->rows[$index - 1]];
    }

    public function moveDown(int $index): void
    {
        if (! isset($this->rows[$index + 1])) {
            return;
        }
        [$this->rows[$index], $this->rows[$index + 1]] = [$this->rows[$index + 1], $this->rows[$index]];
    }

    public function persist(Invitation $invitation): void
    {
        $section = Section::query()
            ->where('invitation_id', $invitation->id)
            ->where('type', 'story')
            ->first();

        if ($section === null) {
            return;
        }

        $content = (array) ($section->content ?? []);
        $content['entries'] = array_values($this->rows);
        $section->content = $content;
        $section->save();
    }
}
