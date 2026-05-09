<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Livewire\Form;

/**
 * 1:many repeater. Each row corresponds to one row in `events`.
 * `id => null` means "new row, insert on persist". Removing a row from the
 * array marks it for deletion (we diff against existing rows by id).
 */
final class EventsForm extends Form
{
    /**
     * @var array<int, array{
     *     id: ?int,
     *     type: string,
     *     name: string,
     *     date: string,
     *     time: ?string,
     *     venue_name: string,
     *     venue_address: ?string,
     *     maps_url: ?string,
     * }>
     */
    public array $rows = [];

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'rows.*.type' => 'required|string|max:32',
            'rows.*.name' => 'required|string|max:120',
            'rows.*.date' => 'required|date',
            'rows.*.time' => 'nullable|string|max:8',
            'rows.*.venue_name' => 'required|string|max:200',
            'rows.*.venue_address' => 'nullable|string|max:500',
            'rows.*.maps_url' => 'nullable|url|max:500',
        ];
    }

    public function fillFromModel(Invitation $invitation): void
    {
        $this->rows = $invitation->events->map(fn (Event $e) => [
            'id' => $e->id,
            'type' => $e->type,
            'name' => $e->name,
            'date' => $e->date->format('Y-m-d'),
            'time' => $e->time !== null ? substr($e->time, 0, 5) : null,
            'venue_name' => $e->venue_name,
            'venue_address' => $e->venue_address,
            'maps_url' => $e->maps_url,
        ])->values()->all();
    }

    public function addRow(): void
    {
        $this->rows[] = [
            'id' => null,
            'type' => 'akad',
            'name' => '',
            'date' => '',
            'time' => null,
            'venue_name' => '',
            'venue_address' => null,
            'maps_url' => null,
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
        DB::transaction(function () use ($invitation) {
            $existingIds = $invitation->events()->pluck('id')->all();
            $keptIds = [];

            foreach ($this->rows as $i => $row) {
                $attributes = [
                    'invitation_id' => $invitation->id,
                    'type' => $row['type'],
                    'name' => $row['name'],
                    'date' => $row['date'],
                    'time' => $row['time'] !== null && $row['time'] !== '' ? $row['time'] : null,
                    'venue_name' => $row['venue_name'],
                    'venue_address' => $row['venue_address'] !== '' ? $row['venue_address'] : null,
                    'maps_url' => $row['maps_url'] !== '' ? $row['maps_url'] : null,
                    'sort_order' => $i,
                ];

                if ($row['id'] !== null && in_array($row['id'], $existingIds, true)) {
                    Event::query()->where('id', $row['id'])->update($attributes);
                    $keptIds[] = $row['id'];
                } else {
                    $created = Event::query()->create($attributes);
                    $this->rows[$i]['id'] = $created->id;
                    $keptIds[] = $created->id;
                }
            }

            $toDelete = array_diff($existingIds, $keptIds);
            if ($toDelete !== []) {
                Event::query()->whereIn('id', $toDelete)->delete();
            }
        });
    }
}
