<?php

declare(strict_types=1);

use App\Models\User;

it('logs the user out and redirects to login', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('login'));

    expect(auth()->check())->toBeFalse();
});

it('rejects logout for guests', function () {
    $this->post('/logout')->assertRedirect(route('login'));
});
