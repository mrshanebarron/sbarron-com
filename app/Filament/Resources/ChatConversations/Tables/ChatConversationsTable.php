<?php

namespace App\Filament\Resources\ChatConversations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session_id')
                    ->searchable(),
                TextColumn::make('ip')
                    ->searchable(),
                TextColumn::make('user_agent')
                    ->searchable(),
                TextColumn::make('first_page')
                    ->searchable(),
                TextColumn::make('turn_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('first_message_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_message_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('emailed_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('flagged_for_review')
                    ->boolean(),
                TextColumn::make('flag_reason')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
