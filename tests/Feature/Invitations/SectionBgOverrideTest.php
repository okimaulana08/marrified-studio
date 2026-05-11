<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Couple;
use App\Models\Invitation;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('invitation_media');
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
    $this->invitation = Invitation::factory()->create();
});

it('persists bg_override into section content when source is couple_bride', function () {
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_name' => 'Anin',
        'groom_name' => 'Farhan',
        'bride_photo_path' => "{$this->invitation->id}/couple/bride.jpg",
    ]);
    $cover = Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'cover',
        'enabled' => true,
        'content' => [],
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->set('sections.rows.0.bg_source', 'couple_bride')
        ->set('sections.rows.0.bg_opacity', 0.7)
        ->set('sections.rows.0.bg_fit', 'cover')
        ->call('saveSections');

    $content = $cover->fresh()->content;
    expect($content['bg_override']['source'])->toBe('couple_bride')
        ->and($content['bg_override']['opacity'])->toEqualWithDelta(0.7, 0.01)
        ->and($content['bg_override']['fit'])->toBe('cover');
});

it('removes bg_override when source is set back to default', function () {
    $cover = Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'cover',
        'enabled' => true,
        'content' => ['bg_override' => ['source' => 'couple_bride', 'opacity' => 0.5, 'fit' => 'cover']],
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->set('sections.rows.0.bg_source', '')
        ->call('saveSections');

    $content = $cover->fresh()->content;
    expect($content)->not->toHaveKey('bg_override');
});

it('clamps opacity to 0..1 range on save', function () {
    $cover = Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'cover',
        'content' => [],
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->set('sections.rows.0.bg_source', 'upload')
        ->set('sections.rows.0.bg_path', "{$this->invitation->id}/sections/{$cover->id}-bg.jpg")
        ->set('sections.rows.0.bg_opacity', 2.5)
        ->call('saveSections');

    expect((float) $cover->fresh()->content['bg_override']['opacity'])->toBe(1.0);
});

it('uploads section background image to invitation_media disk', function () {
    $cover = Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'cover',
        'content' => [],
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->set("sectionBgUpload.0", UploadedFile::fake()->image('cover-bg.jpg', 1200, 800))
        ->call('uploadSectionBg', 0)
        ->assertSet('flashType', 'success');

    $content = $cover->fresh()->content;
    expect($content['bg_override']['source'])->toBe('upload');
    Storage::disk('invitation_media')->assertExists($content['bg_override']['path']);
});

it('removes uploaded section bg file when removeSectionBg is called', function () {
    $cover = Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'cover',
        'content' => [],
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->set('sectionBgUpload.0', UploadedFile::fake()->image('a.jpg', 800, 600))
        ->call('uploadSectionBg', 0);

    $path = $cover->fresh()->content['bg_override']['path'];
    expect(Storage::disk('invitation_media')->exists($path))->toBeTrue();

    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->call('removeSectionBg', 0);

    Storage::disk('invitation_media')->assertMissing($path);
    expect($cover->fresh()->content)->not->toHaveKey('bg_override');
});

it('Section::resolveBgOverride returns null when no override set', function () {
    $cover = Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'cover',
        'content' => [],
    ]);
    $this->invitation->load(['couple', 'sections']);

    expect($cover->resolveBgOverride($this->invitation))->toBeNull();
});

it('Section::resolveBgOverride returns couple bride url when configured', function () {
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_photo_path' => "{$this->invitation->id}/couple/bride.jpg",
    ]);
    Storage::disk('invitation_media')->put("{$this->invitation->id}/couple/bride.jpg", 'fake');

    $cover = Section::factory()->create([
        'invitation_id' => $this->invitation->id,
        'type' => 'cover',
        'content' => ['bg_override' => ['source' => 'couple_bride', 'opacity' => 0.6, 'fit' => 'cover']],
    ]);
    $this->invitation->load(['couple', 'sections']);

    $resolved = $cover->resolveBgOverride($this->invitation);
    expect($resolved)->not->toBeNull()
        ->and($resolved['opacity'])->toEqualWithDelta(0.6, 0.01)
        ->and($resolved['fit'])->toBe('cover')
        ->and($resolved['file_url'])->toContain('bride.jpg');
});
