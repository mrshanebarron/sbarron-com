<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    protected $fillable = [
        'session_id',
        'ip',
        'user_agent',
        'first_page',
        'turn_count',
        'first_message_at',
        'last_message_at',
        'emailed_at',
        'email_error',
        'flagged_for_review',
        'flag_reason',
    ];

    protected $casts = [
        'first_message_at' => 'datetime',
        'last_message_at' => 'datetime',
        'emailed_at' => 'datetime',
        'flagged_for_review' => 'boolean',
        'turn_count' => 'integer',
    ];

    protected $attributes = [
        'turn_count' => 0,
        'flagged_for_review' => false,
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')
            ->orderBy('sent_at');
    }
}
