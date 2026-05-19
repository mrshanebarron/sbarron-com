<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopReferrersTable extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top referrers (last 7 days)';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->buildQuery())
            ->paginated([10, 25])
            ->defaultSort('views', 'desc')
            ->emptyStateHeading('No external referrers yet')
            ->emptyStateDescription('Once someone arrives from outside sbarron.com, the link they came from shows up here.')
            ->columns([
                Tables\Columns\TextColumn::make('referrer_host')
                    ->label('From')
                    ->searchable()
                    ->wrap()
                    ->url(fn ($record) => 'https://' . $record->referrer_host, shouldOpenInNewTab: true),

                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
            ]);
    }

    protected function buildQuery(): Builder
    {
        // Dialect-portable host extraction.
        $driver = DB::connection()->getDriverName();
        $hostExpr = $driver === 'sqlite'
            ? "substr(replace(replace(referrer, 'https://', ''), 'http://', ''), 1, instr(replace(replace(referrer, 'https://', ''), 'http://', '') || '/', '/') - 1)"
            : "SUBSTRING_INDEX(SUBSTRING_INDEX(referrer, '/', 3), '//', -1)";

        return PageView::query()
            ->select([
                DB::raw("$hostExpr as referrer_host"),
                DB::raw('COUNT(*) as views'),
            ])
            ->where('is_bot', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->where('referrer', 'not like', 'https://sbarron.com%')
            ->where('referrer', 'not like', 'https://sbarron.test%')
            ->where('referrer', 'not like', 'http://sbarron.test%')
            ->groupBy('referrer_host');
    }
}
