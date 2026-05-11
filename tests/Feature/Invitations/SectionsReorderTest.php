<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Invitation;
use App\Models\Section;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
    $this->invitation = Invitation::factory()->create();
});

it('reorders sections via drag-and-drop event and persists sort_order', function () {
    // Three sections in original order
    $a = Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'cover', 'sort_order' => 0]);
    $b = Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'couple', 'sort_order' => 1]);
    $c = Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'event', 'sort_order' => 2]);

    // Drag c → first, then a, then b
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->call('reorderSections', [$c->id, $a->id, $b->id])
        ->assertSet('flashType', 'success');

    expect($a->fresh()->sort_order)->toBe(1)
        ->and($b->fresh()->sort_order)->toBe(2)
        ->and($c->fresh()->sort_order)->toBe(0);
});

it('ignores foreign ids and appends missing rows defensively', function () {
    $a = Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'cover', 'sort_order' => 0]);
    $b = Section::factory()->create(['invitation_id' => $this->invitation->id, 'type' => 'couple', 'sort_order' => 1]);

    // Include a fake id 9999 and omit $a — should still place $a at end.
    Livewire::test(InvitationEditor::class, ['invitation' => $this->invitation->fresh()])
        ->call('reorderSections', [$b->id, 9999]);

    expect($b->fresh()->sort_order)->toBe(0)
        ->and($a->fresh()->sort_order)->toBe(1);
});
