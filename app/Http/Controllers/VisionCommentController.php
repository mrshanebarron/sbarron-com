<?php

namespace App\Http\Controllers;

use App\Models\VisionComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Comment submission for the /vision engineering docs.
 *
 * Mirrors ContactController's hardening, plus a moderation gate:
 *   1. Honeypot ("website") filled  -> 200 silently, no row (bot thinks it won).
 *   2. Validate. doc_slug must be one of the known Vision doc slugs — never
 *      trusted blind from the client.
 *   3. Rate-limit per IP (3 comments / 10 min).
 *   4. Persist with approved=FALSE. The comment does NOT appear publicly until
 *      a human approves it — this is the security boundary that makes a public
 *      form safe to run: unapproved input never reaches another reader.
 *   5. Return 200 with a "held for review" message so the visitor knows it
 *      landed and is pending, not lost.
 *
 * Output is escaped at render time (Vue text interpolation + the body is shown
 * as plain text, never v-html), so even an approved comment cannot inject markup.
 */
class VisionCommentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true, 'held' => true]);
        }

        $validated = $request->validate([
            'doc_slug' => 'required|string|max:120',
            'author_name' => 'required|string|max:80',
            'author_email' => 'sometimes|nullable|email|max:200',
            'body' => 'required|string|min:2|max:4000',
        ]);

        // doc_slug must be a real Vision doc — do not trust the client.
        if (! in_array($validated['doc_slug'], VisionDocsController::slugs(), true)) {
            return response()->json(['ok' => false, 'error' => 'Unknown document.'], 422);
        }

        $rlKey = 'vision-comment:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rlKey, 3)) {
            return response()->json([
                'ok' => false,
                'error' => "You've posted a few in a row — give it a few minutes.",
            ], 429);
        }
        RateLimiter::hit($rlKey, 600);

        VisionComment::create([
            'doc_slug' => $validated['doc_slug'],
            'author_name' => $validated['author_name'],
            'author_email' => $validated['author_email'] ?? null,
            'body' => $validated['body'],
            'approved' => false,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return response()->json([
            'ok' => true,
            'held' => true,
            'message' => 'Thanks — your comment is held for review and will appear once approved.',
        ]);
    }
}
