<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Guest;
use Illuminate\Support\Str;

final class GuestToken
{
    private const LENGTH = 10;

    public static function generate(): string
    {
        return Str::random(self::LENGTH);
    }

    public static function ensureUnique(): string
    {
        do {
            $token = self::generate();
        } while (Guest::query()->where('token', $token)->exists());

        return $token;
    }
}
