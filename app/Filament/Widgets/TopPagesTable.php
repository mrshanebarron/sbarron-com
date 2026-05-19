<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopPagesTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top pages (last 7 days, humans only)';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->buildQuery())
            ->paginated([10, 25, 50])
            ->defaultSort('views', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('path')
                    ->label('Path')
                    ->searchable()
                    ->url(fn ($record) => url($record->path), shouldOpenInNewTab: true)
                    ->wrap(),

                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unique_visitors')
                    ->label('Unique IPs')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_seen')
                    ->label('Last view')
                    ->dateTime('M j H:i')
                    ->sortable(),
            ]);
    }

    protected function buildQuery(): Builder
    {
        return PageView::query()
            ->select([
                'path',
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT ip_hash) as unique_visitors'),
                DB::raw('MAX(created_at) as last_seen'),
            ])
            ->where('is_bot', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('path');
    }
}
