<?php

namespace App\Http\Controllers;

use App\Models\PageInteraction;
use App\Support\BotDetector;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Capture endpoint for the click/scroll heatmap.
 *
 * The public site's JS (resources/js/app.js) batches click + scroll
 * events per page visit and POSTs them here. Fire-and-forget: this
 * endpoint always returns 204 and never throws into the visitor's
 * page — analytics must not break the site.
 */
class TrackInteractionController extends Controller
{
    /** Path prefixes we never record (mirror of TrackPageview skip-list). */
    private const SKIP_PREFIXES = [
        'admin', 'livewire', 'filament', 'build', 'storage', '_debugbar', 'api',
    ];

    public function __invoke(Request $request): Response
    {
        try {
            $this->record($request);
        } catch (\Throwable $e) {
            // Never break the visitor's page over analytics.
            Log::warning('TrackInteraction failed', ['error' => $e->getMessage()]);
        }

        return response()->noContent(); // 204
    }

    private function record(Request $request): void
    {
        $path = '/' . ltrim((string) $request->input('path', ''), '/');
        $firstSegment = explode('/', ltrim($path, '/'))[0] ?? '';
        if (in_array($firstSegment, self::SKIP_PREFIXES, true)) {
            return;
        }

        $events = $request->input('events');
        if (! is_array($events) || $events === []) {
            return;
        }

        $viewportW = $this->intInRange($request->input('viewport_w'), 0, 20000);
        $isBot = BotDetector::looksLikeBot($request->userAgent());
        $ipHash = BotDetector::hashIp((string) $request->ip());
        $now = now();
        $pathTrimmed = mb_substr($path, 0, 512);

        $rows = [];
        foreach (array_slice($events, 0, 500) as $event) {
            if (! is_array($event)) {
                continue;
            }
            $type = $event['type'] ?? null;

            if ($type === 'click') {
                $x = $this->intInRange($event['x_pct'] ?? null, 0, 100);
                $y = $this->intInRange($event['y_pct'] ?? null, 0, 100);
                if ($x === null || $y === null) {
                    continue; // malformed/out-of-range click — drop it
                }
                $rows[] = [
                    'path' => $pathTrimmed, 'type' => 'click',
                    'x_pct' => $x, 'y_pct' => $y, 'scroll_pct' => null,
                    'viewport_w' => $viewportW, 'ip_hash' => $ipHash,
                    'is_bot' => $isBot, 'created_at' => $now,
                ];
            } elseif ($type === 'scroll') {
                $depth = $this->intInRange($event['scroll_pct'] ?? null, 0, 100);
                if ($depth === null) {
                    continue; // malformed/out-of-range scroll — drop it
                }
                $rows[] = [
                    'path' => $pathTrimmed, 'type' => 'scroll',
                    'x_pct' => null, 'y_pct' => null, 'scroll_pct' => $depth,
                    'viewport_w' => $viewportW, 'ip_hash' => $ipHash,
                    'is_bot' => $isBot, 'created_at' => $now,
                ];
            }
            // unknown type — silently ignored
        }

        if ($rows !== []) {
            PageInteraction::insert($rows);
        }
    }

    /**
     * Cast to int and require it to fall within [min, max] inclusive.
     * Returns null for non-numeric or out-of-range input.
     */
    private function intInRange(mixed $value, int $min, int $max): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }
        $n = (int) $value;
        return ($n >= $min && $n <= $max) ? $n : null;
    }
}
