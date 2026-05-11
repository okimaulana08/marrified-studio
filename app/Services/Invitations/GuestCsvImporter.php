<?php

declare(strict_types=1);

namespace App\Services\Invitations;

use App\Enums\GuestGroup;
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
    private const MIN_HEADER = ['name', 'relation', 'phone'];

    private const OPTIONAL_HEADER = ['name', 'relation', 'group', 'phone'];

    public const MAX_ROWS = 500;

    /**
     * @return list<array{name: string, relation: string, group: string, phone: string, errors: list<string>}>
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
            $hasGroup = $headerNorm === self::OPTIONAL_HEADER;
            if (! $hasGroup && $headerNorm !== self::MIN_HEADER) {
                throw new RuntimeException('Header CSV harus: name,relation,phone (atau name,relation,group,phone)');
            }

            $rows = [];
            $seenPhones = [];
            $rowNumber = 1;
            $validGroups = array_map(fn ($c) => $c->value, GuestGroup::cases());

            while (($data = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if (count($rows) >= self::MAX_ROWS) {
                    throw new RuntimeException('Maksimal '.self::MAX_ROWS.' baris per file CSV.');
                }

                $name = trim((string) ($data[0] ?? ''));
                $relation = trim((string) ($data[1] ?? ''));
                if ($hasGroup) {
                    $group = strtolower(trim((string) ($data[2] ?? '')));
                    $phone = trim((string) ($data[3] ?? ''));
                } else {
                    $group = '';
                    $phone = trim((string) ($data[2] ?? ''));
                }

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
                if ($group !== '' && ! in_array($group, $validGroups, true)) {
                    $errors[] = "Grup '{$group}' tidak valid (boleh: ".implode(',', $validGroups).')';
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
                    'group' => $group,
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
                    'group' => ($row['group'] ?? '') !== '' ? $row['group'] : null,
                    'phone' => $row['phone'] !== '' ? $row['phone'] : null,
                    'token' => GuestToken::ensureUnique(),
                ]);
                $inserted++;
            }

            return $inserted;
        });
    }
}
