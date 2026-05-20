<?php

namespace App\Support;

/**
 * Shared bot detection + IP hashing for the analytics pipeline.
 *
 * Extracted so page-view tracking (TrackPageview middleware) and
 * interaction tracking (TrackInteractionController) apply the SAME
 * rule — one source of truth. TrackPageview still carries its own
 * copy for now; converging it onto this helper is a follow-up.
 */
class BotDetector
{
    /**
     * Substrings in the User-Agent that mark a known crawler.
     * Case-insensitive match. Kept small to stay fast. Mirrors the
     * list in App\Http\Middleware\TrackPageview.
     */
    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'mediapartners',
        'facebookexternalhit', 'embedly', 'quora link', 'whatsapp',
        'preview', 'fetcher', 'curl/', 'wget/', 'python-requests',
        'monitis', 'pingdom', 'uptimerobot', 'statuscake',
    ];

    public static function looksLikeBot(?string $userAgent): bool
    {
        $ua = (string) $userAgent;
        if ($ua === '') {
            return true;  // empty UA — treat as bot
        }
        $lower = strtolower($ua);
        foreach (self::BOT_PATTERNS as $needle) {
            if (str_contains($lower, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Hash an IP for storage. Salted with the app key so the same IP
     * is stable within this app but not reversible. Matches the
     * formula in TrackPageview. Empty IP returns null.
     */
    public static function hashIp(?string $ip): ?string
    {
        $ip = (string) $ip;
        return $ip === '' ? null : hash('sha256', $ip . config('app.key'));
    }
}
