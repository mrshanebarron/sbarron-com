<?php

namespace Tests\Unit\Models;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit-level checks for the ChatConversation eloquent model.
 *
 * The Feature tests in ChatPersistenceTest cover the HTTP contract.
 * This file pins down model-level invariants:
 *   - mass-assignment guards
 *   - messages() relationship returns rows in sent_at order
 *   - casts behave correctly (datetimes, boolean, integer)
 */
class ChatConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_with_required_fields(): void
    {
        $convo = ChatConversation::create([
            'session_id' => 'sess-unit-1',
            'ip' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 unit',
            'first_page' => '/',
            'turn_count' => 0,
            'first_message_at' => now(),
            'last_message_at' => now(),
        ]);

        $this->assertDatabaseHas('chat_conversations', [
            'session_id' => 'sess-unit-1',
            'ip' => '127.0.0.1',
        ]);
        $this->assertSame(0, $convo->turn_count);
        $this->assertFalse($convo->flagged_for_review);
    }

    public function test_messages_relationship_returns_in_sent_at_order(): void
    {
        $convo = ChatConversation::create([
            'session_id' => 'sess-unit-2',
            'turn_count' => 0,
            'first_message_at' => now(),
            'last_message_at' => now(),
        ]);

        $second = ChatMessage::create([
            'conversation_id' => $convo->id,
            'role' => 'assistant',
            'content' => 'reply',
            'sent_at' => now()->addSecond(),
        ]);

        $first = ChatMessage::create([
            'conversation_id' => $convo->id,
            'role' => 'user',
            'content' => 'hi',
            'sent_at' => now(),
        ]);

        $ordered = $convo->messages()->get();
        $this->assertSame('user', $ordered->first()->role);
        $this->assertSame('assistant', $ordered->last()->role);
    }
}
