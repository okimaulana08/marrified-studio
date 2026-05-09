<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\User;
use App\Services\Invitations\InvitationWriter;

beforeEach(function () {
    $this->writer = new InvitationWriter;
});

it('creates an invitation with valid data', function () {
    $user = User::factory()->couple()->create();
    $inv = $this->writer->create([
        'slug' => 'budi-sari',
        'theme_slug' => 'watercolor-lush',
        'religion_type' => 'islam',
        'user_id' => $user->id,
    ]);

    expect($inv)
        ->slug->toBe('budi-sari')
        ->theme_slug->toBe('watercolor-lush')
        ->user_id->toBe($user->id)
        ->and(Invitation::query()->where('slug', 'budi-sari')->exists())->toBeTrue();
});

it('rejects invalid slug format', function () {
    expect(fn () => $this->writer->create([
        'slug' => 'INVALID UPPER',
        'theme_slug' => 'watercolor-lush',
    ]))->toThrow(RuntimeException::class, 'Invalid slug format');
});

it('rejects duplicate slug', function () {
    Invitation::factory()->create(['slug' => 'taken-slug']);

    expect(fn () => $this->writer->create([
        'slug' => 'taken-slug',
        'theme_slug' => 'watercolor-lush',
    ]))->toThrow(RuntimeException::class, "Slug 'taken-slug' is already taken");
});

it('suggests slug from names', function () {
    $slug = $this->writer->suggestSlug('Sari Dewi', 'Budi Hartono');
    expect($slug)->toBe('sari-dewi-budi-hartono');
});

it('appends suffix on slug collision', function () {
    Invitation::factory()->create(['slug' => 'sari-budi']);

    expect($this->writer->suggestSlug('Sari', 'Budi'))->toBe('sari-budi-2');

    Invitation::factory()->create(['slug' => 'sari-budi-2']);
    expect($this->writer->suggestSlug('Sari', 'Budi'))->toBe('sari-budi-3');
});

it('falls back to "invitation" when names yield empty slug', function () {
    expect($this->writer->suggestSlug('', ''))->toBe('invitation');
});

it('deletes an invitation row', function () {
    $inv = Invitation::factory()->create();

    $this->writer->delete($inv);

    expect(Invitation::query()->find($inv->id))->toBeNull();
});
