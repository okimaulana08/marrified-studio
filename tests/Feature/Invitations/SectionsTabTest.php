<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Invitation;
use App\Models\Section;
use App\Models\User;
use App\Services\Invitations\InvitationWriter;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

it('seeds 8 default sections when creating an invitation through writer', function () {
    $writer = new InvitationWriter;
    $inv = $writer->create([
        'slug' => 'fresh-invite',
        'theme_slug' => 'watercolor-lush',
        'religion_type' => 'islam',
    ]);

    $sections = Section::query()->where('invitation_id', $inv->id)->orderBy('sort_order')->get();
    expect($sections)->toHaveCount(8)
        ->and($sections->pluck('type')->all())->toBe(InvitationWriter::DEFAULT_SECTION_TYPES);

    // Each section should be enabled by default and have a non-empty variant.
    foreach ($sections as $s) {
        expect($s->enabled)->toBeTrue()->and($s->variant)->not->toBeEmpty();
    }
});

it('loads section rows into the form when opening the editor', function () {
    $invitation = Invitation::factory()->create();
    foreach (InvitationWriter::DEFAULT_SECTION_TYPES as $i => $type) {
        Section::factory()->create([
            'invitation_id' => $invitation->id,
            'type' => $type,
            'variant' => 'default',
            'sort_order' => $i,
            'enabled' => true,
        ]);
    }

    $component = Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh(['sections'])]);

    expect($component->get('sections.rows'))->toHaveCount(8);
});

it('toggles enabled and saves to DB', function () {
    $invitation = Invitation::factory()->create();
    $section = Section::factory()->create([
        'invitation_id' => $invitation->id,
        'type' => 'cover', 'variant' => 'arch', 'sort_order' => 0, 'enabled' => true,
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh(['sections'])])
        ->set('sections.rows.0.enabled', false)
        ->call('saveSections')
        ->assertSet('flashType', 'success');

    expect($section->fresh()->enabled)->toBeFalse();
});

it('persists variant change', function () {
    $invitation = Invitation::factory()->create();
    $section = Section::factory()->create([
        'invitation_id' => $invitation->id,
        'type' => 'cover', 'variant' => 'arch', 'sort_order' => 0, 'enabled' => true,
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh(['sections'])])
        ->set('sections.rows.0.variant', 'minimal')
        ->call('saveSections');

    expect($section->fresh()->variant)->toBe('minimal');
});

it('reorders sections via moveSectionDown and persists sort_order', function () {
    $invitation = Invitation::factory()->create();
    $a = Section::factory()->create([
        'invitation_id' => $invitation->id, 'type' => 'cover', 'variant' => 'arch',
        'sort_order' => 0, 'enabled' => true,
    ]);
    $b = Section::factory()->create([
        'invitation_id' => $invitation->id, 'type' => 'quotes', 'variant' => 'default',
        'sort_order' => 1, 'enabled' => true,
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh(['sections'])])
        ->call('moveSectionDown', 0)
        ->call('saveSections');

    expect($a->fresh()->sort_order)->toBe(1)
        ->and($b->fresh()->sort_order)->toBe(0);
});

it('rejects empty variant', function () {
    $invitation = Invitation::factory()->create();
    Section::factory()->create([
        'invitation_id' => $invitation->id, 'type' => 'cover', 'variant' => 'arch',
        'sort_order' => 0, 'enabled' => true,
    ]);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation->fresh(['sections'])])
        ->set('sections.rows.0.variant', '')
        ->call('saveSections')
        ->assertHasErrors(['sections.rows.0.variant']);
});
