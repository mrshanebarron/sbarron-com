<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $base = PageView::query()->where('is_bot', false);

        $today = (clone $base)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        $sevenDay = (clone $base)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $thirtyDay = (clone $base)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $botCount24h = PageView::query()
            ->where('is_bot', true)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return [
            Stat::make('Today', number_format($today))
                ->description('Human pageviews since 00:00')
                ->color('success'),

            Stat::make('7 days', number_format($sevenDay))
                ->description('Human pageviews, last 7 days')
                ->color('primary'),

            Stat::make('30 days', number_format($thirtyDay))
                ->description('Human pageviews, last 30 days')
                ->color('info'),

            Stat::make('Bots (24h)', number_format($botCount24h))
                ->description('Filtered out of the counts above')
                ->color('gray'),
        ];
    }
}
