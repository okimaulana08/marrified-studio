<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Phone number helper for WhatsApp deep-links. Indonesian-first: accepts
 * the common input shapes (08xxx, 8xxx, 62xxx, +62xxx, with spaces or
 * dashes) and normalizes to the bare `62…` form that wa.me expects.
 */
final class PhoneNumber
{
    /**
     * @return string|null Normalized digit-only phone like "6281234567890", or
     *                     null if the input is empty / too short to be a number.
     */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        // Strip everything that isn't a digit (kills spaces, dashes, plus, parens).
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '' || strlen($digits) < 8) {
            return null;
        }

        // Indonesian leading-zero → 62 country code.
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8') && strlen($digits) >= 9 && strlen($digits) <= 13) {
            // Bare local form without leading 0 (e.g. "81234567890") — assume Indonesia.
            $digits = '62'.$digits;
        }

        return $digits;
    }

    /**
     * Build a wa.me deep link. Returns null when phone can't be normalized.
     */
    public static function waLink(?string $rawPhone, string $message = ''): ?string
    {
        $normalized = self::normalize($rawPhone);
        if ($normalized === null) {
            return null;
        }

        $base = "https://wa.me/{$normalized}";
        if ($message === '') {
            return $base;
        }

        return $base.'?text='.rawurlencode($message);
    }
}
