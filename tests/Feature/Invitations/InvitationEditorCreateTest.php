<?php

declare(strict_types=1);

use App\Livewire\Invitations\InvitationEditor;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;

it('admin sees the create page mount with new mode', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.invitations.create'))
        ->assertOk()
        ->assertSee('Buat Invitation Baru');
});

it('couple cannot reach the admin create page', function () {
    $couple = User::factory()->couple()->create();

    $this->actingAs($couple)
        ->get(route('admin.invitations.create'))
        ->assertForbidden();
});

it('creates invitation via BasicTab and redirects to edit', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class)
        ->set('basic.slug', 'new-couple')
        ->set('basic.themeSlug', 'watercolor-lush')
        ->set('basic.religionType', 'islam')
        ->call('save')
        ->assertRedirect(route('invitations.edit', 'new-couple'));

    expect(Invitation::query()->where('slug', 'new-couple')->exists())->toBeTrue();
});

it('rejects invalid slug format', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class)
        ->set('basic.slug', 'BAD UPPER')
        ->set('basic.themeSlug', 'watercolor-lush')
        ->set('basic.religionType', 'islam')
        ->call('save')
        ->assertHasErrors(['basic.slug']);
});

it('rejects duplicate slug', function () {
    Invitation::factory()->create(['slug' => 'taken']);
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class)
        ->set('basic.slug', 'taken')
        ->set('basic.themeSlug', 'watercolor-lush')
        ->set('basic.religionType', 'islam')
        ->call('save')
        ->assertSet('flashType', 'error');
});

it('requires slug, themeSlug, and religionType', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class)
        ->set('basic.slug', '')
        ->set('basic.themeSlug', '')
        ->set('basic.religionType', '')
        ->call('save')
        ->assertHasErrors(['basic.slug', 'basic.themeSlug', 'basic.religionType']);
});

it('admin opens existing editor in edit mode (not new)', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create([
        'slug' => 'existing',
        'theme_slug' => 'watercolor-lush',
        'religion_type' => 'islam',
    ]);

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->assertSet('isNew', false)
        ->assertSet('slug', 'existing')
        ->assertSet('basic.slug', 'existing')
        ->assertSet('basic.themeSlug', 'watercolor-lush')
        ->assertSet('basic.religionType', 'islam');
});

it('couple opens own invitation editor in edit mode', function () {
    $couple = User::factory()->couple()->create();
    $invitation = Invitation::factory()->create([
        'slug' => 'mine',
        'user_id' => $couple->id,
    ]);

    $this->actingAs($couple);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->assertSet('isNew', false)
        ->assertSet('isAdmin', false);
});

it('updating religion_type only is allowed in edit mode', function () {
    $admin = User::factory()->admin()->create();
    $invitation = Invitation::factory()->create([
        'religion_type' => 'islam',
    ]);

    $this->actingAs($admin);

    Livewire::test(InvitationEditor::class, ['invitation' => $invitation])
        ->set('basic.religionType', 'christian')
        ->call('save')
        ->assertSet('flashType', 'success');

    expect($invitation->fresh()->religion_type)->toBe('christian');
});
