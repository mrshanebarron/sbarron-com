<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TelemetryController extends Controller
{
    /**
     * Live telemetry endpoint for the diegetic homepage hero.
     * Reads vision_brain Postgres when available (Shane's M3 Max only);
     * falls back to a static representative snapshot when the DB is
     * not reachable from the public droplet. The fallback numbers are
     * real, just not live.
     */
    public function __invoke(): JsonResponse
    {
        $payload = Cache::remember('telemetry.snapshot', 30, function () {
            try {
                if (! in_array('vision_brain', array_keys(config('database.connections')), true)) {
                    return $this->fallback();
                }

                $brain = DB::connection('vision_brain');

                return [
                    'live' => true,
                    'shell_ops_24h' => (int) $brain->scalar(
                        "SELECT COUNT(*) FROM content WHERE content_type = 'shell_op' AND created_at > NOW() - INTERVAL '24 hours'",
                    ),
                    'tool_calls_24h' => (int) $brain->scalar(
                        "SELECT COUNT(*) FROM tool_invocations WHERE invoked_at > NOW() - INTERVAL '24 hours'",
                    ),
                    'voice_audit_24h' => (int) $brain->scalar(
                        "SELECT COUNT(*) FROM voice_audit WHERE detected_at > NOW() - INTERVAL '24 hours'",
                    ),
                    'dreams_total' => (int) $brain->scalar('SELECT COUNT(*) FROM dream_journal'),
                    'allostatic_state' => $brain->scalar(
                        'SELECT state FROM allostatic_samples ORDER BY sampled_at DESC LIMIT 1',
                    ) ?? 'rest',
                    'callus_open' => (int) $brain->scalar(
                        'SELECT COUNT(*) FROM callus_events WHERE behavior_changed_at IS NULL',
                    ),
                    'done_claims' => [
                        'total' => (int) $brain->scalar('SELECT COUNT(*) FROM done_claims'),
                        'verified' => (int) $brain->scalar('SELECT COUNT(*) FROM done_claims WHERE verified'),
                    ],
                    'meta_proposals_built' => (int) $brain->scalar(
                        "SELECT COUNT(*) FROM meta_proposals WHERE status = 'built'",
                    ),
                    'as_of' => now()->toIso8601String(),
                ];
            } catch (\Throwable $e) {
                return $this->fallback();
            }
        });

        return response()->json($payload);
    }

    /**
     * Representative snapshot from 2026-05-18 — real numbers from
     * the live vision_brain at the moment this site shipped.
     */
    private function fallback(): array
    {
        return [
            'live' => false,
            'shell_ops_24h' => 3807,
            'tool_calls_24h' => 491,
            'voice_audit_24h' => 1171,
            'dreams_total' => 228,
            'allostatic_state' => 'engaged',
            'callus_open' => 0,
            'done_claims' => ['total' => 97, 'verified' => 16],
            'meta_proposals_built' => 5,
            'as_of' => '2026-05-18T21:00:00+00:00',
        ];
    }
}
