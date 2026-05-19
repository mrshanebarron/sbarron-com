<?php

namespace Tests\Unit\Filament\Widgets;

use App\Filament\Widgets\AnalyticsStatsOverview;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test for AnalyticsStatsOverview widget.
 *
 * Contract: produces four Stat objects — Today, 7 days, 30 days, Bots (24h).
 * Today/7d/30d count is_bot=false only. Bots (24h) counts is_bot=true.
 */
class AnalyticsStatsOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_humans_today_and_excludes_bots(): void
    {
        $now = now();
        PageView::create(['path' => '/', 'is_bot' => false, 'created_at' => $now]);
        PageView::create(['path' => '/', 'is_bot' => false, 'created_at' => $now]);
        PageView::create(['path' => '/', 'is_bot' => true, 'created_at' => $now]);

        $widget = new AnalyticsStatsOverview();
        $stats = (function () { return $this->getStats(); })->call($widget);

        $byLabel = [];
        foreach ($stats as $stat) {
            $byLabel[$stat->getLabel()] = $stat;
        }

        $this->assertSame('2', $byLabel['Today']->getValue());
        $this->assertSame('1', $byLabel['Bots (24h)']->getValue());
    }

    public function test_7d_window_excludes_older_views(): void
    {
        $now = now();
        // Inside the window
        PageView::create(['path' => '/', 'is_bot' => false, 'created_at' => $now]);
        // Outside the 7d window
        PageView::create(['path' => '/', 'is_bot' => false, 'created_at' => $now->copy()->subDays(10)]);

        $widget = new AnalyticsStatsOverview();
        $stats = (function () { return $this->getStats(); })->call($widget);
        $byLabel = [];
        foreach ($stats as $stat) {
            $byLabel[$stat->getLabel()] = $stat;
        }

        $this->assertSame('1', $byLabel['7 days']->getValue());
        // 30 day window includes both
        $this->assertSame('2', $byLabel['30 days']->getValue());
    }
}
