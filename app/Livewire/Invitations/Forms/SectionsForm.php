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
        $this->rows = $invitation->sections->map(function (Section $s) {
            $bg = (array) ($s->content['bg_override'] ?? []);

            return [
                'id' => $s->id,
                'type' => $s->type,
                'variant' => $s->variant,
                'enabled' => (bool) $s->enabled,
                'bg_source' => (string) ($bg['source'] ?? ''),
                'bg_gallery_index' => (int) ($bg['gallery_index'] ?? 0),
                'bg_path' => (string) ($bg['path'] ?? ''),
                'bg_opacity' => (float) ($bg['opacity'] ?? 1.0),
                'bg_darken' => (float) ($bg['darken'] ?? 0.0),
                'bg_fit' => (string) ($bg['fit'] ?? 'cover'),
            ];
        })->values()->all();
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
                $section = Section::query()
                    ->where('id', $row['id'])
                    ->where('invitation_id', $invitation->id)
                    ->first();
                if ($section === null) {
                    continue;
                }

                // Merge bg_override into existing content JSON without nuking
                // other content keys (gallery images, story entries, etc).
                $content = (array) ($section->content ?? []);
                $source = (string) ($row['bg_source'] ?? '');
                if ($source === '' || $source === 'default') {
                    unset($content['bg_override']);
                } else {
                    $bg = [
                        'source' => $source,
                        'opacity' => max(0.0, min(1.0, (float) ($row['bg_opacity'] ?? 1.0))),
                        'darken' => max(0.0, min(1.0, (float) ($row['bg_darken'] ?? 0.0))),
                        'fit' => in_array($row['bg_fit'] ?? 'cover', ['cover', 'contain'], true) ? $row['bg_fit'] : 'cover',
                    ];
                    if ($source === 'gallery') {
                        $bg['gallery_index'] = max(0, (int) ($row['bg_gallery_index'] ?? 0));
                    }
                    if ($source === 'upload' && ! empty($row['bg_path'])) {
                        $bg['path'] = (string) $row['bg_path'];
                    }
                    $content['bg_override'] = $bg;
                }

                $section->update([
                    'variant' => $row['variant'],
                    'enabled' => (bool) $row['enabled'],
                    'sort_order' => $i,
                    'content' => $content,
                ]);
            }
        });
    }
}
