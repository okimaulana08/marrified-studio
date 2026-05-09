<?php

declare(strict_types=1);

use App\Models\Guest;
use App\Models\Invitation;
use App\Services\Invitations\GuestCsvImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->importer = new GuestCsvImporter;
    Storage::fake('local');
});

function fakeCsv(string $content): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'csv').'.csv';
    file_put_contents($path, $content);

    return new UploadedFile($path, 'guests.csv', 'text/csv', null, true);
}

it('parses a valid CSV with header and rows', function () {
    $csv = "name,relation,phone\nBudi,Bapak,0812\nSari,Ibu,0813";

    $rows = $this->importer->parse(fakeCsv($csv));

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['name'])->toBe('Budi')
        ->and($rows[0]['relation'])->toBe('Bapak')
        ->and($rows[0]['phone'])->toBe('0812')
        ->and($rows[0]['errors'])->toBe([])
        ->and($rows[1]['name'])->toBe('Sari');
});

it('rejects CSV with wrong header', function () {
    $csv = "nama,relasi,phone\nBudi,Bapak,0812";
    expect(fn () => $this->importer->parse(fakeCsv($csv)))
        ->toThrow(RuntimeException::class, 'Header CSV harus persis: name,relation,phone');
});

it('rejects empty file', function () {
    expect(fn () => $this->importer->parse(fakeCsv('')))
        ->toThrow(RuntimeException::class, 'kosong');
});

it('flags rows with missing name', function () {
    $csv = "name,relation,phone\n,Bapak,0812\nSari,Ibu,";
    $rows = $this->importer->parse(fakeCsv($csv));

    expect($rows[0]['errors'])->toContain('Nama wajib diisi')
        ->and($rows[1]['errors'])->toBe([]);
});

it('flags duplicate phone within the same file', function () {
    $csv = "name,relation,phone\nBudi,Bapak,0812\nSari,Ibu,0812";
    $rows = $this->importer->parse(fakeCsv($csv));

    expect($rows[0]['errors'])->toBe([])
        ->and($rows[1]['errors'])->toHaveCount(1);
    expect($rows[1]['errors'][0])->toContain('duplikat');
});

it('rejects file exceeding 500 rows', function () {
    $lines = ['name,relation,phone'];
    for ($i = 1; $i <= GuestCsvImporter::MAX_ROWS + 1; $i++) {
        $lines[] = "Guest{$i},Tamu,";
    }
    expect(fn () => $this->importer->parse(fakeCsv(implode("\n", $lines))))
        ->toThrow(RuntimeException::class, 'Maksimal');
});

it('imports valid rows transactionally with unique tokens', function () {
    $invitation = Invitation::factory()->create();
    $rows = [
        ['name' => 'Budi', 'relation' => 'Bapak', 'phone' => '0812', 'errors' => []],
        ['name' => 'Sari', 'relation' => 'Ibu', 'phone' => '0813', 'errors' => []],
    ];

    $count = $this->importer->import($invitation, $rows);

    expect($count)->toBe(2);
    $guests = Guest::query()->where('invitation_id', $invitation->id)->get();
    expect($guests)->toHaveCount(2)
        ->and($guests->pluck('token')->unique()->count())->toBe(2);
});

it('skips rows that have errors during import', function () {
    $invitation = Invitation::factory()->create();
    $rows = [
        ['name' => 'Budi', 'relation' => 'Bapak', 'phone' => '0812', 'errors' => []],
        ['name' => '', 'relation' => 'X', 'phone' => '0813', 'errors' => ['Nama wajib diisi']],
    ];

    $count = $this->importer->import($invitation, $rows);

    expect($count)->toBe(1)
        ->and(Guest::query()->where('invitation_id', $invitation->id)->count())->toBe(1);
});

it('defaults relation to "Tamu" when blank', function () {
    $invitation = Invitation::factory()->create();
    $rows = [
        ['name' => 'NoRelation', 'relation' => '', 'phone' => '', 'errors' => []],
    ];

    $this->importer->import($invitation, $rows);

    expect(Guest::query()->where('invitation_id', $invitation->id)->first()->relation)->toBe('Tamu');
});
