<?php

declare(strict_types=1);

use App\Livewire\Invitations\ActivityTab;
use App\Models\Couple;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
    $this->invitation = Invitation::factory()->create();
});

it('logs an activity when invitation is updated', function () {
    $this->invitation->update(['slug' => 'fresh-slug-99']);

    $log = Activity::query()->where('subject_type', Invitation::class)
        ->where('subject_id', $this->invitation->id)
        ->where('event', 'updated')
        ->latest()
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->log_name)->toBe('invitation');
});

it('logs an activity when couple is created', function () {
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_name' => 'Anin',
        'groom_name' => 'Farhan',
    ]);

    $log = Activity::query()->where('subject_type', Couple::class)
        ->where('log_name', 'couple')
        ->where('event', 'created')
        ->latest()
        ->first();

    expect($log)->not->toBeNull();
});

it('shows activity entries belonging to the invitation', function () {
    // Generate a couple-related activity
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_name' => 'X',
        'groom_name' => 'Y',
    ]);
    // And an invitation-level update
    $this->invitation->update(['theme_slug' => $this->invitation->theme_slug]);

    Livewire::test(ActivityTab::class, ['invitationId' => $this->invitation->id])
        ->assertSee('Riwayat Aktivitas');
});

it('filters activity by log_name', function () {
    Couple::factory()->create([
        'invitation_id' => $this->invitation->id,
        'bride_name' => 'X',
        'groom_name' => 'Y',
    ]);
    $this->invitation->update(['slug' => 'something-different']);

    $component = Livewire::test(ActivityTab::class, ['invitationId' => $this->invitation->id])
        ->set('logFilter', 'couple');

    $activities = $component->viewData('activities');
    foreach ($activities as $a) {
        expect($a->log_name)->toBe('couple');
    }
});

it('forbids unauthorized users', function () {
    $stranger = User::factory()->couple()->create();
    $this->actingAs($stranger);

    Livewire::test(ActivityTab::class, ['invitationId' => $this->invitation->id])
        ->assertForbidden();
});
