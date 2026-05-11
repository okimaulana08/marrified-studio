<?php

declare(strict_types=1);

use App\Livewire\Invitations\GuestsTab;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\User;
use App\Services\Invitations\GuestCsvImporter;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
    $this->invitation = Invitation::factory()->create();
});

it('adds a guest with group via form', function () {
    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->set('form.name', 'Budi')
        ->set('form.group', 'family')
        ->call('addGuest');

    $g = Guest::query()->where('invitation_id', $this->invitation->id)->firstOrFail();
    expect($g->group)->toBe('family');
});

it('rejects an invalid group value', function () {
    Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->set('form.name', 'Budi')
        ->set('form.group', 'not-a-real-group')
        ->call('addGuest')
        ->assertHasErrors(['form.group']);
});

it('filters guest list by selected group', function () {
    Guest::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Bapak Keluarga', 'group' => 'family']);
    Guest::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Teman Lama', 'group' => 'friend']);
    Guest::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Tanpa Grup', 'group' => null]);

    $html = Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->set('groupFilter', 'family')
        ->html();

    expect($html)->toContain('Bapak Keluarga')
        ->and($html)->not->toContain('Teman Lama')
        ->and($html)->not->toContain('Tanpa Grup');
});

it('filters to "none" for guests without a group', function () {
    Guest::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Punya Grup', 'group' => 'friend']);
    Guest::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Tanpa Grup', 'group' => null]);

    $html = Livewire::test(GuestsTab::class, [
        'invitationId' => $this->invitation->id,
        'isAdmin' => true,
    ])
        ->set('groupFilter', 'none')
        ->html();

    expect($html)->toContain('Tanpa Grup')
        ->and($html)->not->toContain('Punya Grup');
});

it('imports CSV with optional group column', function () {
    $importer = app(GuestCsvImporter::class);

    $csv = "name,relation,group,phone\nBudi Keluarga,Bapak,family,0812\nTanpa,Sahabat,,0813";
    $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

    $parsed = $importer->parse($file);
    expect($parsed)->toHaveCount(2)
        ->and($parsed[0]['group'])->toBe('family')
        ->and($parsed[1]['group'])->toBe('');

    $count = $importer->import($this->invitation, $parsed);
    expect($count)->toBe(2);

    expect(Guest::query()->where('name', 'Budi Keluarga')->first()->group)->toBe('family');
    expect(Guest::query()->where('name', 'Tanpa')->first()->group)->toBeNull();
});

it('flags invalid group in CSV row as error', function () {
    $importer = app(GuestCsvImporter::class);

    $csv = "name,relation,group,phone\nBudi,Bapak,fake_group,0812";
    $file = UploadedFile::fake()->createWithContent('bad.csv', $csv);

    $parsed = $importer->parse($file);
    expect($parsed[0]['errors'])->not->toBeEmpty();
});
