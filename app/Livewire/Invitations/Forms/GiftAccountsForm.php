<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Models\GiftAccount;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Livewire\Form;

/**
 * 1:many repeater for bank/ewallet accounts shown in the gift section.
 * Same diff-on-persist pattern as EventsForm — id=null means new row.
 */
final class GiftAccountsForm extends Form
{
    /**
     * @var array<int, array{
     *     id: ?int,
     *     type: string,
     *     bank_name: string,
     *     account_number: string,
     *     account_name: string,
     * }>
     */
    public array $rows = [];

    public function rules(): array
    {
        return [
            'rows.*.type' => 'required|string|in:bank,ewallet,other',
            'rows.*.bank_name' => 'required|string|max:60',
            'rows.*.account_number' => 'required|string|max:40',
            'rows.*.account_name' => 'required|string|max:120',
        ];
    }

    public function fillFromModel(Invitation $invitation): void
    {
        $this->rows = $invitation->giftAccounts->map(fn (GiftAccount $g) => [
            'id' => $g->id,
            'type' => $g->type,
            'bank_name' => $g->bank_name,
            'account_number' => $g->account_number,
            'account_name' => $g->account_name,
        ])->values()->all();
    }

    public function addRow(): void
    {
        $this->rows[] = [
            'id' => null,
            'type' => 'bank',
            'bank_name' => '',
            'account_number' => '',
            'account_name' => '',
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
            $existingIds = $invitation->giftAccounts()->pluck('id')->all();
            $keptIds = [];

            foreach ($this->rows as $i => $row) {
                $attributes = [
                    'invitation_id' => $invitation->id,
                    'type' => $row['type'],
                    'bank_name' => $row['bank_name'],
                    'account_number' => $row['account_number'],
                    'account_name' => $row['account_name'],
                    'sort_order' => $i,
                ];

                if ($row['id'] !== null && in_array($row['id'], $existingIds, true)) {
                    GiftAccount::query()->where('id', $row['id'])->update($attributes);
                    $keptIds[] = $row['id'];
                } else {
                    $created = GiftAccount::query()->create($attributes);
                    $this->rows[$i]['id'] = $created->id;
                    $keptIds[] = $created->id;
                }
            }

            $toDelete = array_diff($existingIds, $keptIds);
            if ($toDelete !== []) {
                GiftAccount::query()->whereIn('id', $toDelete)->delete();
            }
        });
    }
}
