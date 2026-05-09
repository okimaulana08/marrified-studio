<?php

declare(strict_types=1);

use App\Livewire\Invitations\Forms\GalleryForm;
use App\Livewire\Invitations\InvitationEditor;
use App\Models\Section;
use App\Models\User;
use App\Services\Invitations\InvitationWriter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('invitation_media');
    $this->actingAs(User::factory()->admin()->create());
    $this->invitation = (new InvitationWriter)->create([
        'slug' => 'gallery-test',
        'theme_slug' => 'watercolor-lush',
        'religion_type' => 'islam',
    ]);
});

it('starts with empty gallery when no images saved', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->assertSet('gallery.images', []);
});

it('uploads multiple photos and persists paths to Section.content', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('newGalleryPhotos', [
            UploadedFile::fake()->image('a.jpg', 800, 600),
            UploadedFile::fake()->image('b.png', 800, 600),
        ])
        ->call('uploadGalleryPhotos')
        ->assertSet('flashType', 'success')
        ->assertSet('newGalleryPhotos', []);

    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'gallery')
        ->firstOrFail();

    $images = $section->content['images'] ?? [];
    expect($images)->toHaveCount(2);

    foreach ($images as $path) {
        expect($path)->toStartWith("{$this->invitation->id}/gallery/");
        Storage::disk('invitation_media')->assertExists($path);
    }
});

it('caps additions at MAX_IMAGES and skips overflow', function () {
    // Pre-fill 19 images so only 1 slot is left.
    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'gallery')
        ->firstOrFail();
    $section->content = ['images' => array_map(
        fn ($i) => "{$this->invitation->id}/gallery/{$i}.jpg",
        range(1, GalleryForm::MAX_IMAGES - 1),
    )];
    $section->save();

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh(['sections'])])
        ->set('newGalleryPhotos', [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.jpg'),
            UploadedFile::fake()->image('c.jpg'),
        ])
        ->call('uploadGalleryPhotos')
        ->assertSet('flashType', 'success');

    $stored = $section->fresh()->content['images'];
    expect($stored)->toHaveCount(GalleryForm::MAX_IMAGES);
});

it('rejects upload when already at cap', function () {
    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'gallery')
        ->firstOrFail();
    $section->content = ['images' => array_map(
        fn ($i) => "{$this->invitation->id}/gallery/{$i}.jpg",
        range(1, GalleryForm::MAX_IMAGES),
    )];
    $section->save();

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh(['sections'])])
        ->set('newGalleryPhotos', [UploadedFile::fake()->image('over.jpg')])
        ->call('uploadGalleryPhotos')
        ->assertSet('flashType', 'error');
});

it('rejects oversized files via validation', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('newGalleryPhotos', [UploadedFile::fake()->create('huge.jpg', GalleryForm::MAX_FILE_KB + 100)])
        ->call('uploadGalleryPhotos')
        ->assertHasErrors(['newGalleryPhotos.0']);
});

it('rejects disallowed extensions', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('newGalleryPhotos', [UploadedFile::fake()->create('evil.gif', 200)])
        ->call('uploadGalleryPhotos')
        ->assertHasErrors(['newGalleryPhotos.0']);
});

it('removes image from disk and array', function () {
    $component = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('newGalleryPhotos', [UploadedFile::fake()->image('a.jpg')])
        ->call('uploadGalleryPhotos');

    $path = $component->get('gallery.images.0');
    Storage::disk('invitation_media')->assertExists($path);

    $component->call('removeGalleryPhoto', 0)
        ->assertSet('gallery.images', []);

    Storage::disk('invitation_media')->assertMissing($path);
});

it('reorders images via moveDown and persists order', function () {
    $component = Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('newGalleryPhotos', [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.jpg'),
        ])
        ->call('uploadGalleryPhotos');

    $first = $component->get('gallery.images.0');
    $second = $component->get('gallery.images.1');

    $component->call('moveGalleryDown', 0)
        ->call('saveGalleryOrder');

    $section = Section::query()
        ->where('invitation_id', $this->invitation->id)
        ->where('type', 'gallery')
        ->firstOrFail();

    expect($section->content['images'])->toBe([$second, $first]);
});

it('couple owner can manage their gallery', function () {
    $owner = User::factory()->couple()->create();
    $invitation = (new InvitationWriter)->create([
        'slug' => 'owner-gallery',
        'theme_slug' => 'watercolor-lush',
        'religion_type' => 'islam',
        'user_id' => $owner->id,
    ]);

    $this->actingAs($owner);
    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('newGalleryPhotos', [UploadedFile::fake()->image('mine.jpg')])
        ->call('uploadGalleryPhotos')
        ->assertSet('flashType', 'success');

    expect(Section::query()
        ->where('invitation_id', $invitation->id)
        ->where('type', 'gallery')
        ->value('content')['images'] ?? [])->toHaveCount(1);
});

it('media is cascade-deleted by InvitationObserver when invitation is deleted', function () {
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('newGalleryPhotos', [UploadedFile::fake()->image('a.jpg')])
        ->call('uploadGalleryPhotos');

    Storage::disk('invitation_media')
        ->assertExists("{$this->invitation->id}/gallery/".basename(Section::query()
            ->where('invitation_id', $this->invitation->id)
            ->where('type', 'gallery')
            ->firstOrFail()
            ->content['images'][0]));

    $this->invitation->delete();

    expect(Storage::disk('invitation_media')->directoryExists((string) $this->invitation->id))->toBeFalse();
});
