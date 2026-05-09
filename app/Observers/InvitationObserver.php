<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invitation;
use Illuminate\Support\Facades\Storage;

/**
 * Cascades invitation deletion to its media directory on the `invitation_media`
 * disk. Database rows in couples/events/guests/etc. are already cascaded by
 * FK ON DELETE CASCADE — this observer handles only the filesystem cleanup
 * that the DB can't reach.
 */
final class InvitationObserver
{
    public function deleting(Invitation $invitation): void
    {
        $disk = Storage::disk('invitation_media');
        $dir = (string) $invitation->id;

        if ($disk->directoryExists($dir)) {
            $disk->deleteDirectory($dir);
        }
    }
}
