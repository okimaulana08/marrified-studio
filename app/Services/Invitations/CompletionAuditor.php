<?php

declare(strict_types=1);

namespace App\Services\Invitations;

use App\Models\Invitation;

/**
 * Read-only audit of an invitation's completeness. Used by the editor header
 * gauge to nudge couples toward filling everything that matters — but it is
 * NOT a publish gate. All invitations remain publicly accessible regardless
 * of percentage.
 *
 * Returns:
 *   - percent: rounded 0–100 across all weighted items
 *   - items:   keyed by tab name, each {label, done, weight}
 *   - todos:   subset of items where done=false, for the editor dropdown
 */
final class CompletionAuditor
{
    /**
     * @return array{percent: int, items: array<string, array{label: string, done: bool, tab: string, weight: int}>, todos: array<int, array{label: string, tab: string}>}
     */
    public function audit(Invitation $invitation): array
    {
        $invitation->loadMissing(['couple', 'events', 'sections', 'giftAccounts', 'guests']);

        $sectionsByType = $invitation->sections->keyBy('type');
        $sectionEnabled = fn (string $type) => (bool) ($sectionsByType[$type]->enabled ?? false);

        $couple = $invitation->couple;
        $events = $invitation->events;

        $items = [
            'couple_names' => [
                'tab' => 'couple',
                'label' => 'Nama pengantin lengkap',
                'done' => $couple !== null
                    && trim((string) $couple->bride_name) !== ''
                    && trim((string) $couple->groom_name) !== '',
                'weight' => 3,
            ],
            'couple_photos' => [
                'tab' => 'couple',
                'label' => 'Foto kedua pengantin',
                'done' => $couple !== null
                    && $couple->bride_photo_path !== null
                    && $couple->groom_photo_path !== null,
                'weight' => 2,
            ],
            'events' => [
                'tab' => 'events',
                'label' => 'Minimal 1 acara dengan tanggal & venue',
                'done' => $events->isNotEmpty()
                    && $events->contains(fn ($e) => $e->date !== null && trim((string) $e->venue_name) !== ''),
                'weight' => 3,
            ],
            'guests' => [
                'tab' => 'guests',
                'label' => 'Minimal 1 tamu di daftar',
                'done' => $invitation->guests->isNotEmpty(),
                'weight' => 2,
            ],
        ];

        // Conditional items — hanya hitung kalau section enabled.
        if ($sectionEnabled('story')) {
            $storyEntries = (array) ($sectionsByType['story']->content['entries'] ?? []);
            $items['story'] = [
                'tab' => 'stories',
                'label' => 'Cerita Cinta diisi (minimal 1 entry)',
                'done' => count($storyEntries) > 0,
                'weight' => 1,
            ];
        }

        if ($sectionEnabled('gift')) {
            $items['gift'] = [
                'tab' => 'gift',
                'label' => 'Rekening hadiah diisi',
                'done' => $invitation->giftAccounts->isNotEmpty(),
                'weight' => 1,
            ];
        }

        if ($sectionEnabled('gallery')) {
            $images = (array) ($sectionsByType['gallery']->content['images'] ?? []);
            $items['gallery'] = [
                'tab' => 'gallery',
                'label' => 'Galeri foto (minimal 1 foto)',
                'done' => count($images) > 0,
                'weight' => 1,
            ];
        }

        // Music optional, only flag if section is enabled.
        if ($sectionEnabled('music') || $invitation->music_track_id !== null) {
            $items['music'] = [
                'tab' => 'music',
                'label' => 'Track musik dipilih',
                'done' => $invitation->music_track_id !== null,
                'weight' => 1,
            ];
        }

        if ($sectionEnabled('thanks')) {
            $message = (string) ($sectionsByType['thanks']->content['message'] ?? '');
            $items['thanks'] = [
                'tab' => 'thanks',
                'label' => 'Pesan penutup diisi',
                'done' => trim($message) !== '',
                'weight' => 1,
            ];
        }

        // Percentage = weighted-done / weighted-total.
        $total = array_sum(array_column($items, 'weight'));
        $done = array_sum(array_map(
            fn (array $i) => $i['done'] ? $i['weight'] : 0,
            $items
        ));
        $percent = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        $todos = [];
        foreach ($items as $key => $i) {
            if (! $i['done']) {
                $todos[] = ['label' => $i['label'], 'tab' => $i['tab']];
            }
        }

        return [
            'percent' => $percent,
            'items' => $items,
            'todos' => $todos,
        ];
    }
}
