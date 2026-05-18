<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /api/telemetry powers the diegetic homepage hero. When vision_brain
 * is reachable it returns live numbers; when not (i.e. on the public
 * droplet), it returns a representative fallback snapshot.
 */
class TelemetryControllerTest extends TestCase
{
    public function test_telemetry_endpoint_returns_expected_shape(): void
    {
        $resp = $this->get('/api/telemetry');
        $resp->assertOk();

        $resp->assertJsonStructure([
            'live',
            'shell_ops_24h',
            'tool_calls_24h',
            'voice_audit_24h',
            'dreams_total',
            'allostatic_state',
            'callus_open',
            'done_claims' => ['total', 'verified'],
            'meta_proposals_built',
            'as_of',
        ]);
    }

    public function test_fallback_numbers_are_nonzero_and_plausible(): void
    {
        // Force fallback by ensuring vision_brain connection is misconfigured
        // for the test runner. The fallback values are real numbers we want to
        // see on the public site even when the DB is not reachable.
        $resp = $this->get('/api/telemetry');
        $data = $resp->json();

        $this->assertGreaterThan(0, $data['shell_ops_24h']);
        $this->assertGreaterThan(0, $data['dreams_total']);
        $this->assertContains($data['allostatic_state'], ['rest', 'engaged', 'strained', 'overloaded', 'depleted']);
    }
}
