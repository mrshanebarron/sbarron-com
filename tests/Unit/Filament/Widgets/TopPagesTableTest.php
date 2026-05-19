<?php

namespace Tests\Unit\Filament\Widgets;

use App\Filament\Widgets\TopPagesTable;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test for TopPagesTable widget.
 *
 * Contract: groups by path, counts views and unique IPs, restricts
 * to last 7 days, excludes bots.
 */
class TopPagesTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_groups_by_path_and_ranks_by_views(): void
    {
        $now = now();
        PageView::create(['path' => '/writing/substrate-is-the-body', 'is_bot' => false, 'ip_hash' => 'a', 'created_at' => $now]);
        PageView::create(['path' => '/writing/substrate-is-the-body', 'is_bot' => false, 'ip_hash' => 'b', 'created_at' => $now]);
        PageView::create(['path' => '/writing/substrate-is-the-body', 'is_bot' => false, 'ip_hash' => 'a', 'created_at' => $now]);
        PageView::create(['path' => '/', 'is_bot' => false, 'ip_hash' => 'a', 'created_at' => $now]);

        $widget = new TopPagesTable();
        $rows = (function () { return $this->buildQuery(); })->call($widget)->get();

        $top = $rows->sortByDesc('views')->first();
        $this->assertSame('/writing/substrate-is-the-body', $top->path);
        $this->assertSame(3, (int) $top->views);
        $this->assertSame(2, (int) $top->unique_visitors);
    }

    public function test_excludes_bots(): void
    {
        $now = now();
        PageView::create(['path' => '/x', 'is_bot' => true, 'ip_hash' => 'a', 'created_at' => $now]);

        $widget = new TopPagesTable();
        $rows = (function () { return $this->buildQuery(); })->call($widget)->get();

        $this->assertCount(0, $rows);
    }

    public function test_excludes_views_older_than_7_days(): void
    {
        $now = now();
        PageView::create(['path' => '/old', 'is_bot' => false, 'ip_hash' => 'a', 'created_at' => $now->copy()->subDays(10)]);

        $widget = new TopPagesTable();
        $rows = (function () { return $this->buildQuery(); })->call($widget)->get();

        $this->assertCount(0, $rows);
    }
}
