<?php

declare(strict_types=1);

namespace App\Services\Invitations;

use App\Models\Guest;
use App\Models\Invitation;
use App\Support\GuestToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Parses + imports a guest list CSV with header `name,relation,phone`.
 * `parse()` is pure: returns rows + per-row error tags. The UI shows a
 * preview, the user confirms, then `import()` does the bulk insert in a
 * transaction with token generation per row.
 */
final class GuestCsvImporter
{
    private const EXPECTED_HEADER = ['name', 'relation', 'phone'];

    public const MAX_ROWS = 500;

    /**
     * @return list<array{name: string, relation: string, phone: string, errors: list<string>}>
     */
    public function parse(UploadedFile $file): array
    {
        $handle = @fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new RuntimeException('Tidak bisa membaca file CSV.');
        }

        try {
            $header = fgetcsv($handle);
            if ($header === false) {
                throw new RuntimeException('File CSV kosong.');
            }

            $headerNorm = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
            if ($headerNorm !== self::EXPECTED_HEADER) {
                throw new RuntimeException('Header CSV harus persis: name,relation,phone');
            }

            $rows = [];
            $seenPhones = [];
            $rowNumber = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if (count($rows) >= self::MAX_ROWS) {
                    throw new RuntimeException('Maksimal '.self::MAX_ROWS.' baris per file CSV.');
                }

                $name = trim((string) ($data[0] ?? ''));
                $relation = trim((string) ($data[1] ?? ''));
                $phone = trim((string) ($data[2] ?? ''));

                $errors = [];
                if ($name === '') {
                    $errors[] = 'Nama wajib diisi';
                }
                if (mb_strlen($name) > 120) {
                    $errors[] = 'Nama melebihi 120 karakter';
                }
                if (mb_strlen($relation) > 60) {
                    $errors[] = 'Relasi melebihi 60 karakter';
                }
                if ($phone !== '' && mb_strlen($phone) > 30) {
                    $errors[] = 'Phone melebihi 30 karakter';
                }
                if ($phone !== '' && isset($seenPhones[$phone])) {
                    $errors[] = 'Phone duplikat di baris '.$seenPhones[$phone];
                } elseif ($phone !== '') {
                    $seenPhones[$phone] = $rowNumber;
                }

                $rows[] = [
                    'name' => $name,
                    'relation' => $relation,
                    'phone' => $phone,
                    'errors' => $errors,
                ];
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Inserts only valid rows (those without errors). Returns count inserted.
     *
     * @param  list<array{name: string, relation: string, phone: string, errors: list<string>}>  $rows
     */
    public function import(Invitation $invitation, array $rows): int
    {
        return DB::transaction(function () use ($invitation, $rows) {
            $inserted = 0;
            foreach ($rows as $row) {
                if (! empty($row['errors'])) {
                    continue;
                }

                Guest::query()->create([
                    'invitation_id' => $invitation->id,
                    'name' => $row['name'],
                    'relation' => $row['relation'] !== '' ? $row['relation'] : 'Tamu',
                    'phone' => $row['phone'] !== '' ? $row['phone'] : null,
                    'token' => GuestToken::ensureUnique(),
                ]);
                $inserted++;
            }

            return $inserted;
        });
    }
}
