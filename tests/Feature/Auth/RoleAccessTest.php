<?php

declare(strict_types=1);

use App\Models\User;

it('allows admin to access admin theme list', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/themes')
        ->assertOk();
});

it('forbids couple from accessing admin theme list', function () {
    $couple = User::factory()->couple()->create();

    $this->actingAs($couple)
        ->get('/admin/themes')
        ->assertForbidden();
});

it('redirects guests from admin to login', function () {
    $this->get('/admin/themes')->assertRedirect(route('login'));
});

it('forbids couple from accessing admin invitations list', function () {
    $couple = User::factory()->couple()->create();

    $this->actingAs($couple)
        ->get('/admin/invitations')
        ->assertForbidden();
});
