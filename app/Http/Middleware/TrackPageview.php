<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackPageview
{
    /**
     * Paths we don't want in the analytics ledger.
     * Admin/internal panels, build assets, and well-known files.
     */
    private const SKIP_PREFIXES = [
        'admin',
        'livewire',
        'filament',
        'build',
        'storage',
        '_debugbar',
        'api',
    ];

    private const SKIP_EXACT = [
        'favicon.ico',
        'robots.txt',
        'sitemap.xml',
        'apple-touch-icon.png',
        'apple-touch-icon-precomposed.png',
    ];

    /**
     * Substrings in the User-Agent that mark a known crawler.
     * Match is case-insensitive; we keep the list small to stay fast.
     */
    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'mediapartners',
        'facebookexternalhit', 'embedly', 'quora link', 'whatsapp',
        'preview', 'fetcher', 'curl/', 'wget/', 'python-requests',
        'monitis', 'pingdom', 'uptimerobot', 'statuscake',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->record($request, $response);
        } catch (\Throwable $e) {
            // Analytics must never break the response. Log and move on.
            Log::warning('TrackPageview failed', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    private function record(Request $request, Response $response): void
    {
        if ($request->method() !== 'GET') {
            return;
        }

        $path = trim($request->path(), '/');
        if ($path === '') {
            $path = '/';
        }

        // Skip-list check
        $firstSegment = explode('/', ltrim($path, '/'))[0] ?? '';
        if (in_array($firstSegment, self::SKIP_PREFIXES, true)) {
            return;
        }
        if (in_array($path, self::SKIP_EXACT, true)) {
            return;
        }

        // Only count HTML responses — skip JSON/inertia-json, images, files.
        $contentType = strtolower($response->headers->get('Content-Type', ''));
        if ($contentType !== '' && !str_contains($contentType, 'text/html')) {
            return;
        }

        $ua = (string) $request->userAgent();
        $referrer = (string) $request->headers->get('referer', '');
        $ip = (string) $request->ip();

        PageView::create([
            'path' => mb_substr('/' . ltrim($path, '/'), 0, 512),
            'url' => mb_substr($request->fullUrl(), 0, 2048),
            'referrer' => $referrer === '' ? null : mb_substr($referrer, 0, 2048),
            'user_agent' => $ua === '' ? null : mb_substr($ua, 0, 512),
            'ip_hash' => $ip === '' ? null : hash('sha256', $ip . config('app.key')),
            'is_bot' => $this->looksLikeBot($ua),
            'created_at' => now(),
        ]);
    }

    private function looksLikeBot(string $ua): bool
    {
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
}
