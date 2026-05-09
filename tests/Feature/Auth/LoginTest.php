<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\User;

it('renders login page for guests', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Marrified Studio')
        ->assertSee('Email');
});

it('redirects authenticated user away from login page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/login')
        ->assertRedirect(route('admin.invitations.index'));
});

it('logs in admin and redirects to admin invitations list', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@test.test',
        'password' => 'secret-password',
    ]);

    $this->post('/login', [
        'email' => 'admin@test.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('admin.invitations.index'));

    expect(auth()->id())->toBe($admin->id);
});

it('logs in couple with linked invitation and redirects to their editor', function () {
    $user = User::factory()->couple()->create([
        'email' => 'couple@test.test',
        'password' => 'secret-password',
    ]);
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
        'slug' => 'foo-bar',
    ]);

    $this->post('/login', [
        'email' => 'couple@test.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('invitations.edit', $invitation->slug));
});

it('logs in couple without linked invitation and redirects to dashboard placeholder', function () {
    $user = User::factory()->couple()->create([
        'email' => 'orphan@test.test',
        'password' => 'secret-password',
    ]);

    $this->post('/login', [
        'email' => 'orphan@test.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('dashboard'));
});

it('rejects login with bad credentials', function () {
    User::factory()->admin()->create([
        'email' => 'admin@test.test',
        'password' => 'correct-password',
    ]);

    $this->post('/login', [
        'email' => 'admin@test.test',
        'password' => 'wrong-password',
    ])
        ->assertSessionHasErrors('email')
        ->assertRedirect();

    expect(auth()->check())->toBeFalse();
});

it('requires email and password fields', function () {
    $this->post('/login', [])
        ->assertSessionHasErrors(['email', 'password']);
});
