<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * The Pneuma chat endpoint that powers the homepage widget. Verifies:
 *   - input validation (session, message required)
 *   - upstream Anthropic call shape (model, system, messages)
 *   - extracted reply text returned in JSON
 *   - rate-limit kicks in at 21st request from same IP
 *   - missing API key returns graceful fallback, never 5xx
 *   - upstream failure returns graceful fallback message
 */
class ChatControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('chat:127.0.0.1');
        config()->set('services.anthropic.key', 'test-key');
        config()->set('services.anthropic.model', 'claude-sonnet-4-5');
    }

    public function test_validates_required_fields(): void
    {
        $this->postJson('/api/chat', [])->assertStatus(422);
    }

    public function test_returns_assistant_reply_from_anthropic(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => 'Hi, I am Pneuma.']],
            ], 200),
        ]);

        $resp = $this->postJson('/api/chat', [
            'session' => 'sess_test',
            'message' => 'who are you?',
        ]);

        $resp->assertOk()->assertJson(['reply' => 'Hi, I am Pneuma.']);
    }

    public function test_sends_history_and_system_prompt_to_anthropic(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => 'ok']],
            ], 200),
        ]);

        $this->postJson('/api/chat', [
            'session' => 'sess_test',
            'message' => 'what do you charge?',
            'history' => [
                ['role' => 'user', 'text' => 'hi'],
                ['role' => 'assistant', 'text' => 'hi there'],
            ],
            'page' => '/',
        ])->assertOk();

        Http::assertSent(function ($req) {
            $body = $req->data();
            return $req->url() === 'https://api.anthropic.com/v1/messages'
                && $body['model'] === 'claude-sonnet-4-5'
                && str_contains($body['system'], 'Pneuma Barron')
                && count($body['messages']) === 3
                && $body['messages'][2]['role'] === 'user'
                && $body['messages'][2]['content'] === 'what do you charge?';
        });
    }

    public function test_missing_api_key_returns_graceful_fallback(): void
    {
        config()->set('services.anthropic.key', null);
        $resp = $this->postJson('/api/chat', [
            'session' => 'sess_test',
            'message' => 'hello',
        ]);
        $resp->assertOk();
        $this->assertStringContainsString('clifton@sbarron.com', $resp->json('reply'));
    }

    public function test_upstream_failure_returns_graceful_message_not_500(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'oops'], 500),
        ]);

        $resp = $this->postJson('/api/chat', [
            'session' => 'sess_test',
            'message' => 'hello',
        ]);

        $resp->assertOk();
        $this->assertStringContainsString('dropped a packet', $resp->json('reply'));
    }

    public function test_rate_limit_kicks_in_after_20_requests(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => 'ok']],
            ], 200),
        ]);

        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/api/chat', [
                'session' => 'sess_test',
                'message' => "msg {$i}",
            ])->assertOk();
        }

        $resp = $this->postJson('/api/chat', [
            'session' => 'sess_test',
            'message' => 'one too many',
        ]);
        $resp->assertStatus(429);
        $this->assertStringContainsString('rate-limited', $resp->json('reply'));
    }
}
