<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\User;
use App\Policies\InvitationPolicy;

beforeEach(function () {
    $this->policy = new InvitationPolicy;
});

it('admin can view any list', function () {
    expect($this->policy->viewAny(User::factory()->admin()->make()))->toBeTrue()
        ->and($this->policy->viewAny(User::factory()->couple()->make()))->toBeFalse();
});

it('admin can view, update, and delete any invitation', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create();

    expect($this->policy->view($admin, $invitation))->toBeTrue()
        ->and($this->policy->update($admin, $invitation))->toBeTrue()
        ->and($this->policy->delete($admin, $invitation))->toBeTrue()
        ->and($this->policy->create($admin))->toBeTrue();
});

it('couple can view and update only their own invitation', function () {
    $owner = User::factory()->couple()->create();
    $stranger = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->view($owner, $invitation))->toBeTrue()
        ->and($this->policy->update($owner, $invitation))->toBeTrue()
        ->and($this->policy->view($stranger, $invitation))->toBeFalse()
        ->and($this->policy->update($stranger, $invitation))->toBeFalse();
});

it('couple cannot delete or create regardless of ownership', function () {
    $owner = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->delete($owner, $invitation))->toBeFalse()
        ->and($this->policy->create($owner))->toBeFalse();
});

it('orphan invitation (no user_id) is editable by admin only', function () {
    $admin = User::factory()->admin()->create();
    $couple = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create(['user_id' => null]);

    expect($this->policy->view($admin, $invitation))->toBeTrue()
        ->and($this->policy->update($admin, $invitation))->toBeTrue()
        ->and($this->policy->view($couple, $invitation))->toBeFalse()
        ->and($this->policy->update($couple, $invitation))->toBeFalse();
});
