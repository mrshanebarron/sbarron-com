<?php

namespace Tests\Unit\Filament\Widgets;

use App\Filament\Widgets\TrafficHeatmap;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behavior tests for the TrafficHeatmap widget.
 *
 * Contract:
 *   - getViewData() returns a 7x24 grid (dow 0=Mon..6=Sun, hour 0..23).
 *   - Each cell is the count of HUMAN pageviews in that day/hour bucket,
 *     last 30 days. Bots and views older than 30 days are excluded.
 *   - `max` is the largest single-cell count; `total` is the sum.
 *
 * We assert on the returned grid (observable output of the widget),
 * not on private query internals.
 */
class TrafficHeatmapTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Invoke the protected getViewData() on a widget instance.
     *
     * @return array{grid: array<int, array<int, int>>, max: int, total: int, days: array<int, string>}
     */
    private function viewData(): array
    {
        $widget = new TrafficHeatmap();

        return (function () {
            return $this->getViewData();
        })->call($widget);
    }

    public function test_grid_is_seven_by_twentyfour(): void
    {
        $data = $this->viewData();

        $this->assertCount(7, $data['grid']);
        foreach ($data['grid'] as $hours) {
            $this->assertCount(24, $hours);
        }
        $this->assertSame(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], $data['days']);
    }

    public function test_empty_when_no_views(): void
    {
        $data = $this->viewData();

        $this->assertSame(0, $data['max']);
        $this->assertSame(0, $data['total']);
    }

    public function test_human_view_lands_in_correct_day_and_hour_bucket(): void
    {
        // Monday 2026-05-18 at 14:00 local.
        $monday2pm = \Illuminate\Support\Carbon::parse('2026-05-18 14:00:00');

        PageView::create([
            'path' => '/',
            'is_bot' => false,
            'ip_hash' => 'a',
            'created_at' => $monday2pm,
        ]);

        $data = $this->viewData();

        // dow 0 = Monday, hour 14.
        $this->assertSame(1, $data['grid'][0][14]);
        $this->assertSame(1, $data['total']);
        $this->assertSame(1, $data['max']);
    }

    public function test_bots_are_excluded_from_the_grid(): void
    {
        $monday2pm = \Illuminate\Support\Carbon::parse('2026-05-18 14:00:00');

        PageView::create(['path' => '/', 'is_bot' => false, 'ip_hash' => 'a', 'created_at' => $monday2pm]);
        PageView::create(['path' => '/', 'is_bot' => true, 'ip_hash' => 'b', 'created_at' => $monday2pm]);

        $data = $this->viewData();

        // Only the human view counts.
        $this->assertSame(1, $data['grid'][0][14]);
        $this->assertSame(1, $data['total']);
    }

    public function test_views_older_than_thirty_days_are_excluded(): void
    {
        PageView::create([
            'path' => '/',
            'is_bot' => false,
            'ip_hash' => 'a',
            'created_at' => now()->subDays(45),
        ]);

        $data = $this->viewData();

        $this->assertSame(0, $data['total']);
    }

    public function test_multiple_views_same_bucket_accumulate(): void
    {
        $sunday9am = \Illuminate\Support\Carbon::parse('2026-05-17 09:00:00');

        foreach (['a', 'b', 'c'] as $ip) {
            PageView::create(['path' => '/', 'is_bot' => false, 'ip_hash' => $ip, 'created_at' => $sunday9am]);
        }

        $data = $this->viewData();

        // dow 6 = Sunday, hour 9.
        $this->assertSame(3, $data['grid'][6][9]);
        $this->assertSame(3, $data['max']);
        $this->assertSame(3, $data['total']);
    }
}
