<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use App\Support\InvitationQr;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves QR code PNGs for the invitation main URL and per-guest token URLs.
 * Authorization: matches InvitationPolicy::update — admin always, couple on
 * own invitation only. Files cached via App\Support\InvitationQr.
 */
final class QrCodeController extends Controller
{
    /** Main invitation QR — encodes URL without token. */
    public function invitation(string $slug): BinaryFileResponse
    {
        $invitation = Invitation::query()->where('slug', $slug)->firstOrFail();
        Gate::authorize('update', $invitation);

        $path = InvitationQr::forInvitation($invitation);

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="qr-'.$slug.'.png"',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /** Per-guest QR — encodes the token URL. */
    public function guest(string $slug, int $guestId): BinaryFileResponse
    {
        $invitation = Invitation::query()->where('slug', $slug)->firstOrFail();
        Gate::authorize('update', $invitation);

        $guest = Guest::query()
            ->where('invitation_id', $invitation->id)
            ->findOrFail($guestId);

        $path = InvitationQr::forGuest($guest, $invitation);

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="qr-'.$slug.'-'.$guest->token.'.png"',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
