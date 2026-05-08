<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Contracts\View\View;

final class PublishedInvitationController extends Controller
{
    public function __invoke(string $slug, ?string $token = null): View
    {
        $invitation = Invitation::query()
            ->with([
                'couple',
                'events',
                'sections' => fn ($q) => $q->where('enabled', true)->orderBy('sort_order'),
                'giftAccounts',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $theme = app(ThemeRegistry::class)->find($invitation->theme_slug)
            ?? abort(404, 'Theme not found');

        $guest = null;
        if ($token !== null) {
            $guest = Guest::query()
                ->where('invitation_id', $invitation->id)
                ->where('token', $token)
                ->first();

            if ($guest) {
                $guest->increment('opens_count');
                $guest->forceFill([
                    'first_opened_at' => $guest->first_opened_at ?? now(),
                    'last_opened_at' => now(),
                ])->save();
            }
        }

        return view('layouts.render', compact('invitation', 'theme', 'guest'));
    }
}
