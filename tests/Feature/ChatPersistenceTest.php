<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Behavior tests for chat persistence + session-end email.
 *
 * Contract:
 *   1. Every /api/chat POST creates or updates a chat_conversations row
 *      keyed by session_id, with one chat_messages row per turn (user + assistant).
 *   2. Hitting /api/chat/end with the session_id sends a transcript to
 *      mrshanebarron@gmail.com and marks emailed_at on the conversation.
 *   3. Repeated /api/chat/end calls for the same session are idempotent
 *      (emailed_at set once, no duplicate mails).
 */
class ChatPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        // Force the API key path off so ChatController falls back to its
        // deterministic stub reply — we're testing persistence, not the
        // upstream Anthropic call.
        config(['services.anthropic.api_key' => null]);
    }

    public function test_first_message_creates_conversation_with_two_messages(): void
    {
        $resp = $this->postJson('/api/chat', [
            'session' => 'sess-aaa',
            'message' => 'Hello Pneuma',
            'page' => '/',
        ]);

        $resp->assertOk();

        $this->assertDatabaseCount('chat_conversations', 1);
        $convo = ChatConversation::where('session_id', 'sess-aaa')->first();
        $this->assertNotNull($convo);
        $this->assertSame(1, $convo->turn_count);
        $this->assertSame(2, $convo->messages()->count()); // user + assistant
        $this->assertSame('user', $convo->messages()->first()->role);
        $this->assertSame('Hello Pneuma', $convo->messages()->first()->content);
    }

    public function test_subsequent_messages_append_to_same_conversation(): void
    {
        $this->postJson('/api/chat', [
            'session' => 'sess-bbb',
            'message' => 'first',
            'page' => '/',
        ])->assertOk();

        $this->postJson('/api/chat', [
            'session' => 'sess-bbb',
            'message' => 'second',
            'page' => '/about',
        ])->assertOk();

        $this->assertDatabaseCount('chat_conversations', 1);
        $convo = ChatConversation::where('session_id', 'sess-bbb')->first();
        $this->assertSame(2, $convo->turn_count);
        $this->assertSame(4, $convo->messages()->count());
    }

    public function test_session_end_emails_transcript_and_marks_emailed(): void
    {
        $this->postJson('/api/chat', [
            'session' => 'sess-end',
            'message' => 'I want a quote',
            'page' => '/',
        ])->assertOk();

        $resp = $this->postJson('/api/chat/end', [
            'session' => 'sess-end',
        ]);

        $resp->assertOk();

        $convo = ChatConversation::where('session_id', 'sess-end')->first();
        $this->assertNotNull($convo->emailed_at);
        $this->assertNull($convo->email_error);
    }

    public function test_session_end_idempotent_no_duplicate_email(): void
    {
        $this->postJson('/api/chat', [
            'session' => 'sess-dupe',
            'message' => 'hi',
            'page' => '/',
        ])->assertOk();

        $this->postJson('/api/chat/end', ['session' => 'sess-dupe'])->assertOk();
        $firstEmailedAt = ChatConversation::where('session_id', 'sess-dupe')->first()->emailed_at;
        $this->assertNotNull($firstEmailedAt);

        Carbon::setTestNow(now()->addSeconds(30));
        $this->postJson('/api/chat/end', ['session' => 'sess-dupe'])->assertOk();

        // emailed_at must not change on subsequent ends — first email wins
        $secondEmailedAt = ChatConversation::where('session_id', 'sess-dupe')->first()->emailed_at;
        $this->assertEquals(
            $firstEmailedAt->toDateTimeString(),
            $secondEmailedAt->toDateTimeString(),
            'Second /api/chat/end must be idempotent — emailed_at must not move.'
        );
    }

    public function test_session_end_without_messages_does_not_email(): void
    {
        // No chat happened, just /api/chat/end called (e.g. user opened widget,
        // closed tab without typing). Nothing to email.
        $resp = $this->postJson('/api/chat/end', [
            'session' => 'sess-empty',
        ]);

        $resp->assertOk();
        $this->assertDatabaseCount('chat_conversations', 0);
    }
}
