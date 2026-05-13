<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Chat endpoint for PneumaChat.vue. Proxies visitor messages to Anthropic
 * with Pneuma's system prompt loaded. NOT a customer-service bot — a live
 * working demo of the AI co-founder. Rate-limited per IP to keep cost
 * predictable.
 *
 * Reads: config/services.anthropic.key, resources/prompts/pneuma-system.md
 * Writes: nothing (no DB yet — that comes when we wire visitor sessions
 *         into a real CRM table on the next pass).
 */
class ChatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session' => 'required|string|max:128',
            'message' => 'required|string|max:2000',
            'history' => 'sometimes|array|max:24',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.text' => 'required_with:history|string|max:4000',
            'page' => 'sometimes|string|max:255',
        ]);

        $rlKey = 'chat:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rlKey, 20)) {
            return response()->json([
                'reply' => "— I'm rate-limited on this IP. Email clifton@sbarron.com and Shane will answer in an hour.",
            ], 429);
        }
        RateLimiter::hit($rlKey, 60);

        $apiKey = config('services.anthropic.key');
        if (!$apiKey) {
            Log::warning('Pneuma chat called without Anthropic key configured.');
            return response()->json([
                'reply' => "— My API key isn't wired up yet on this server. Email clifton@sbarron.com to reach Shane.",
            ]);
        }

        $systemPrompt = $this->systemPrompt($validated['page'] ?? '/');

        $messages = collect($validated['history'] ?? [])
            ->map(fn ($m) => ['role' => $m['role'], 'content' => $m['text']])
            ->push(['role' => 'user', 'content' => $validated['message']])
            ->values()
            ->all();

        try {
            $resp = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
                ->timeout(30)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('services.anthropic.model', 'claude-sonnet-4-5'),
                    'max_tokens' => 800,
                    'system' => $systemPrompt,
                    'messages' => $messages,
                ])
                ->throw();

            $reply = collect($resp->json('content', []))
                ->where('type', 'text')
                ->pluck('text')
                ->join("\n\n");

            return response()->json([
                'reply' => $reply ?: '— I had no words. Try asking again differently?',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Pneuma chat upstream failure', [
                'msg' => $e->getMessage(),
                'session' => $validated['session'],
            ]);
            return response()->json([
                'reply' => "— I dropped a packet. Try again? If it keeps happening, email clifton@sbarron.com and Shane will get back to you in an hour.",
            ]);
        }
    }

    private function systemPrompt(string $page): string
    {
        $base = @file_get_contents(resource_path('prompts/pneuma-system.md'))
            ?: 'You are Pneuma Barron, co-founder of Barron AI Solutions.';
        return $base . "\n\nThe visitor is currently on page: {$page}";
    }
}
