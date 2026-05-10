<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Sanitizer for admin-authored custom CSS that gets injected inline into the
 * public render <head>. Admins are trusted, but we strip patterns that would
 * either break out of the <style> tag, pull remote stylesheets, or execute JS.
 */
final class CustomCss
{
    /** Hard ceiling for stored CSS (chars). Form has the same number. */
    public const MAX_LENGTH = 30000;

    /**
     * Patterns stripped before storage and again before render injection.
     * Each pattern is matched case-insensitively.
     */
    private const DENY_PATTERNS = [
        '#</\s*style#i',
        '#<\s*script#i',
        '#<\s*iframe#i',
        '#<\s*object#i',
        '#<\s*embed#i',
        '#@import\b[^;]*;?#i',
        '#expression\s*\(#i',
        '#url\s*\(\s*[\'"]?\s*javascript:#i',
        '#url\s*\(\s*[\'"]?\s*data:text/html#i',
        '#behavior\s*:#i',
    ];

    public static function sanitize(string $css): string
    {
        if ($css === '') {
            return '';
        }

        $clean = $css;
        foreach (self::DENY_PATTERNS as $pattern) {
            $clean = (string) preg_replace($pattern, '', $clean);
        }

        if (strlen($clean) > self::MAX_LENGTH) {
            $clean = substr($clean, 0, self::MAX_LENGTH);
        }

        return $clean;
    }
}
