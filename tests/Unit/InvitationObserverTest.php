<?php

declare(strict_types=1);

use App\Models\Invitation;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('invitation_media');
});

it('deletes the media directory when an invitation is deleted', function () {
    $invitation = Invitation::factory()->create();

    Storage::disk('invitation_media')->put("{$invitation->id}/couple/bride.webp", 'fake');
    Storage::disk('invitation_media')->put("{$invitation->id}/gallery/01H.webp", 'fake');

    expect(Storage::disk('invitation_media')->directoryExists((string) $invitation->id))->toBeTrue();

    $invitation->delete();

    expect(Storage::disk('invitation_media')->directoryExists((string) $invitation->id))->toBeFalse();
});

it('does not error when no media directory exists', function () {
    $invitation = Invitation::factory()->create();

    expect(fn () => $invitation->delete())->not->toThrow(Throwable::class);
});
