<?php

namespace App\Filament\Resources\ChatConversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChatConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('session_id')
                    ->required(),
                TextInput::make('ip'),
                TextInput::make('user_agent'),
                TextInput::make('first_page'),
                TextInput::make('turn_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('first_message_at'),
                DateTimePicker::make('last_message_at'),
                DateTimePicker::make('emailed_at'),
                Textarea::make('email_error')
                    ->columnSpanFull(),
                Toggle::make('flagged_for_review')
                    ->required(),
                TextInput::make('flag_reason'),
            ]);
    }
}
