<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Models\Invitation;
use App\Models\Section;
use App\Services\Themes\VariantScanner;
use Illuminate\Support\Facades\DB;
use Livewire\Form;

/**
 * Section enable/disable + variant picker + reorder.
 *
 * Section types are FIXED (8 types pre-seeded by InvitationWriter::create) —
 * the user only toggles, picks variants, and reorders them. They never add
 * or remove section rows. Available variants are scanned from the filesystem
 * via VariantScanner so newly-added blade variants surface automatically.
 */
final class SectionsForm extends Form
{
    /**
     * @var array<int, array{
     *     id: int,
     *     type: string,
     *     variant: string,
     *     enabled: bool,
     * }>
     */
    public array $rows = [];

    public function rules(): array
    {
        return [
            'rows.*.variant' => 'required|string|max:64',
        ];
    }

    public function fillFromModel(Invitation $invitation): void
    {
        $this->rows = $invitation->sections->map(fn (Section $s) => [
            'id' => $s->id,
            'type' => $s->type,
            'variant' => $s->variant,
            'enabled' => (bool) $s->enabled,
        ])->values()->all();
    }

    /**
     * Variant options grouped by section type, scanned from blade filesystem.
     *
     * @return array<string, list<string>>
     */
    public function variantOptions(VariantScanner $scanner): array
    {
        $map = [];
        foreach ($this->rows as $row) {
            if (! isset($map[$row['type']])) {
                $map[$row['type']] = $scanner->forType($row['type']);
            }
        }

        return $map;
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

    /**
     * Re-sort `$rows` to match the given ID sequence (e.g. from a drag-and-drop UI).
     * IDs not in the current row set are ignored; any current rows missing from
     * the input list are appended at the end so we never lose a row.
     *
     * @param  list<int|string>  $orderedIds
     */
    public function reorderTo(array $orderedIds): void
    {
        $byId = [];
        foreach ($this->rows as $row) {
            $byId[(int) $row['id']] = $row;
        }

        $reordered = [];
        foreach ($orderedIds as $id) {
            $intId = (int) $id;
            if (isset($byId[$intId])) {
                $reordered[] = $byId[$intId];
                unset($byId[$intId]);
            }
        }
        // Append any rows that weren't in the input list (defensive).
        foreach ($byId as $row) {
            $reordered[] = $row;
        }

        $this->rows = $reordered;
    }

    public function persist(Invitation $invitation): void
    {
        DB::transaction(function () use ($invitation) {
            foreach ($this->rows as $i => $row) {
                Section::query()
                    ->where('id', $row['id'])
                    ->where('invitation_id', $invitation->id)
                    ->update([
                        'variant' => $row['variant'],
                        'enabled' => (bool) $row['enabled'],
                        'sort_order' => $i,
                    ]);
            }
        });
    }
}
