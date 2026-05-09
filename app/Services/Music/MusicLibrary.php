<?php

declare(strict_types=1);

namespace App\Services\Music;

use App\Models\MusicTrack;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Admin-curated music library. Wraps the disk + DB lifecycle of a music track:
 *   - upload(): stores file on `music_assets` disk with a ULID filename and
 *     creates the corresponding MusicTrack row in a single transaction.
 *   - delete(): removes the file from disk + the DB row. FK ON DELETE SET NULL
 *     on invitations.music_track_id automatically nullifies the link in any
 *     invitation that was using this track.
 */
final class MusicLibrary
{
    public const DISK = 'music_assets';

    public const MAX_FILE_KB = 8192; // 8 MB

    /**
     * @param  array{title: string, artist?: ?string, uploaded_by?: ?int}  $meta
     */
    public function upload(UploadedFile $file, array $meta): MusicTrack
    {
        $filename = (string) Str::ulid().'.mp3';
        $relativePath = "tracks/{$filename}";

        return DB::transaction(function () use ($file, $meta, $relativePath, $filename) {
            $file->storeAs('tracks', $filename, self::DISK);

            return MusicTrack::query()->create([
                'uploaded_by' => $meta['uploaded_by'] ?? null,
                'title' => trim((string) $meta['title']),
                'artist' => isset($meta['artist']) && trim((string) $meta['artist']) !== ''
                    ? trim((string) $meta['artist'])
                    : null,
                'file_path' => $relativePath,
                'duration_seconds' => null, // Phase C initial: skip getid3 parsing.
            ]);
        });
    }

    public function delete(MusicTrack $track): void
    {
        DB::transaction(function () use ($track) {
            $disk = Storage::disk(self::DISK);
            if ($track->file_path !== '' && $disk->exists($track->file_path)) {
                $disk->delete($track->file_path);
            }

            // Cascade ON DELETE SET NULL takes care of any invitation that
            // had this track linked.
            $track->delete();
        });
    }
}
