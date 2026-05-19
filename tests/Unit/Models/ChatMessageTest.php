<?php

namespace Tests\Unit\Models;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_persists_attached_to_conversation(): void
    {
        $convo = ChatConversation::create([
            'session_id' => 'sess-msg',
            'turn_count' => 0,
            'first_message_at' => now(),
            'last_message_at' => now(),
        ]);

        ChatMessage::create([
            'conversation_id' => $convo->id,
            'role' => 'user',
            'content' => 'hello',
            'sent_at' => now(),
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $convo->id,
            'role' => 'user',
            'content' => 'hello',
        ]);
    }
}
