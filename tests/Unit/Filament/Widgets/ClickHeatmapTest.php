<?php

namespace Tests\Unit\Filament\Widgets;

use App\Filament\Widgets\ClickHeatmap;
use App\Models\PageInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behavior tests for the ClickHeatmap widget.
 *
 * Contract:
 *   - getViewData() returns, for the selected path:
 *       * a 20-wide x 20-tall grid of click counts (clicks bucketed by
 *         x_pct / y_pct into 5%-wide cells),
 *       * `clickTotal` — total clicks counted,
 *       * `scrollAvg` / `scrollViewers` — average max-scroll-depth and
 *         how many scroll events fed it,
 *       * `paths` — distinct paths that have click data, for the picker.
 *   - Bots are excluded. Only the selected path's events count.
 */
class ClickHeatmapTest extends TestCase
{
    use RefreshDatabase;

    private function widgetData(string $path): array
    {
        $widget = new ClickHeatmap();
        $widget->path = $path;

        return (function () {
            return $this->getViewData();
        })->call($widget);
    }

    public function test_empty_when_no_interactions(): void
    {
        $data = $this->widgetData('/');

        $this->assertSame(0, $data['clickTotal']);
        $this->assertSame(0, $data['scrollViewers']);
        $this->assertCount(20, $data['grid']);
    }

    public function test_clicks_bucket_into_the_grid_for_the_selected_path(): void
    {
        // Two clicks near top-left, one near bottom-right, all on /writing.
        PageInteraction::create(['path' => '/writing', 'type' => 'click', 'x_pct' => 2, 'y_pct' => 3, 'is_bot' => false]);
        PageInteraction::create(['path' => '/writing', 'type' => 'click', 'x_pct' => 4, 'y_pct' => 1, 'is_bot' => false]);
        PageInteraction::create(['path' => '/writing', 'type' => 'click', 'x_pct' => 97, 'y_pct' => 96, 'is_bot' => false]);
        // A click on a different path must not appear.
        PageInteraction::create(['path' => '/', 'type' => 'click', 'x_pct' => 50, 'y_pct' => 50, 'is_bot' => false]);

        $data = $this->widgetData('/writing');

        $this->assertSame(3, $data['clickTotal']);
        // x_pct 2 and 4 both fall in column 0 (0-4%); y_pct 3 and 1 in row 0.
        $this->assertSame(2, $data['grid'][0][0]);
        // x_pct 97 -> column 19, y_pct 96 -> row 19.
        $this->assertSame(1, $data['grid'][19][19]);
    }

    public function test_bot_clicks_are_excluded(): void
    {
        PageInteraction::create(['path' => '/', 'type' => 'click', 'x_pct' => 10, 'y_pct' => 10, 'is_bot' => false]);
        PageInteraction::create(['path' => '/', 'type' => 'click', 'x_pct' => 10, 'y_pct' => 10, 'is_bot' => true]);

        $data = $this->widgetData('/');

        $this->assertSame(1, $data['clickTotal']);
    }

    public function test_scroll_depth_is_averaged_over_human_scroll_events(): void
    {
        PageInteraction::create(['path' => '/', 'type' => 'scroll', 'scroll_pct' => 40, 'is_bot' => false]);
        PageInteraction::create(['path' => '/', 'type' => 'scroll', 'scroll_pct' => 80, 'is_bot' => false]);
        PageInteraction::create(['path' => '/', 'type' => 'scroll', 'scroll_pct' => 90, 'is_bot' => true]); // bot — ignored

        $data = $this->widgetData('/');

        $this->assertSame(2, $data['scrollViewers']);
        $this->assertSame(60, $data['scrollAvg']); // (40 + 80) / 2
    }

    public function test_paths_list_contains_distinct_human_click_paths(): void
    {
        PageInteraction::create(['path' => '/', 'type' => 'click', 'x_pct' => 1, 'y_pct' => 1, 'is_bot' => false]);
        PageInteraction::create(['path' => '/writing', 'type' => 'click', 'x_pct' => 1, 'y_pct' => 1, 'is_bot' => false]);
        PageInteraction::create(['path' => '/writing', 'type' => 'click', 'x_pct' => 2, 'y_pct' => 2, 'is_bot' => false]);

        $data = $this->widgetData('/');

        $this->assertContains('/', $data['paths']);
        $this->assertContains('/writing', $data['paths']);
        $this->assertSame(count($data['paths']), count(array_unique($data['paths'])));
    }
}
