<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

/**
 * Traffic heatmap — human pageviews bucketed by day-of-week x hour-of-day,
 * rendered as a 7x24 grid. Cell intensity scales with view count so the
 * busy windows of the week are visible at a glance.
 *
 * Uses created_at only; no new tracking. Humans only (is_bot = 0),
 * last 30 days.
 */
class TrafficHeatmap extends Widget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.traffic-heatmap';

    /**
     * @return array{grid: array<int, array<int, int>>, max: int, total: int, days: array<int, string>}
     */
    protected function getViewData(): array
    {
        // MySQL DAYOFWEEK: 1=Sunday..7=Saturday. SQLite strftime('%w'): 0=Sunday..6.
        // Normalize both to 0=Monday..6=Sunday for a Mon-first grid.
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $dowExpr = "((CAST(strftime('%w', created_at) AS INTEGER) + 6) % 7)";
            $hourExpr = "CAST(strftime('%H', created_at) AS INTEGER)";
        } else {
            $dowExpr = '((DAYOFWEEK(created_at) + 5) % 7)';
            $hourExpr = 'HOUR(created_at)';
        }

        $rows = PageView::query()
            ->where('is_bot', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw("$dowExpr as dow, $hourExpr as hr, COUNT(*) as views")
            ->groupBy('dow', 'hr')
            ->get();

        // grid[dow][hour] = count; default 0
        $grid = [];
        for ($d = 0; $d < 7; $d++) {
            $grid[$d] = array_fill(0, 24, 0);
        }

        $max = 0;
        $total = 0;
        foreach ($rows as $row) {
            $d = (int) $row->dow;
            $h = (int) $row->hr;
            $v = (int) $row->views;
            if ($d >= 0 && $d < 7 && $h >= 0 && $h < 24) {
                $grid[$d][$h] = $v;
                $total += $v;
                if ($v > $max) {
                    $max = $v;
                }
            }
        }

        return [
            'grid' => $grid,
            'max' => $max,
            'total' => $total,
            'days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        ];
    }
}
