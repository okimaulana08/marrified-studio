<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Guest;
use App\Models\Invitation;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Generate QR code PNGs for invitations and individual guests. Results are
 * cached on the public storage disk under storage/app/public/qr/{hash}.png
 * so concurrent visitors don't keep re-rendering the same image.
 */
final class InvitationQr
{
    private const CACHE_DIR = 'qr';

    private const SIZE = 600;

    private const MARGIN = 12;

    /** Build a QR for the main invitation URL (no guest token). */
    public static function forInvitation(Invitation $invitation): string
    {
        $url = url('/'.$invitation->slug);
        $hash = hash('xxh64', 'inv:'.$invitation->id.':'.$url);

        return self::buildOrCache($url, $hash);
    }

    /** Build a per-guest QR — encodes the token URL so opens_count fires. */
    public static function forGuest(Guest $guest, Invitation $invitation): string
    {
        $url = url('/'.$invitation->slug.'/'.$guest->token);
        $hash = hash('xxh64', 'g:'.$guest->id.':'.$url);

        return self::buildOrCache($url, $hash);
    }

    /** Returns the public URL of the cached image. */
    public static function urlFor(string $data, string $cacheKey): string
    {
        $hash = hash('xxh64', $cacheKey);
        self::buildOrCache($data, $hash);

        return Storage::disk('public')->url(self::CACHE_DIR.'/'.$hash.'.png');
    }

    /** Returns the absolute filesystem path to the cached file, generating if missing. */
    private static function buildOrCache(string $data, string $hash): string
    {
        $relativePath = self::CACHE_DIR.'/'.$hash.'.png';
        $disk = Storage::disk('public');

        if (! $disk->exists($relativePath)) {
            $builder = new Builder(
                writer: new PngWriter,
                data: $data,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: self::SIZE,
                margin: self::MARGIN,
            );
            $result = $builder->build();
            $disk->put($relativePath, $result->getString());
        }

        return $disk->path($relativePath);
    }

    /** Invalidate cached QR for an invitation (e.g. on slug change). */
    public static function invalidateForInvitation(Invitation $invitation): void
    {
        $disk = Storage::disk('public');
        $dir = $disk->path(self::CACHE_DIR);

        if (! is_dir($dir)) {
            return;
        }

        // Conservative cleanup: scan and remove files older than now that contain
        // invitation id signature is not feasible since hash is opaque. Just wipe
        // the whole qr/ folder; will regenerate on next visit. Used rarely.
        File::deleteDirectory($dir, preserve: true);
    }
}
