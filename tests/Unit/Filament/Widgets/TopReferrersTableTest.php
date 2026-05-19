<?php

namespace Tests\Unit\Filament\Widgets;

use App\Filament\Widgets\TopReferrersTable;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test for TopReferrersTable widget.
 *
 * Contract: groups by referrer hostname, excludes internal referrers
 * (sbarron.com / sbarron.test), excludes bots, restricts to last 7d.
 */
class TopReferrersTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_referrers_are_included(): void
    {
        PageView::create([
            'path' => '/writing/substrate-is-the-body',
            'is_bot' => false,
            'referrer' => 'https://news.ycombinator.com/item?id=123',
            'created_at' => now(),
        ]);

        $widget = new TopReferrersTable();
        $rows = (function () { return $this->buildQuery(); })->call($widget)->get();

        $this->assertContains('news.ycombinator.com', $rows->pluck('referrer_host')->toArray());
    }

    public function test_internal_referrers_are_excluded(): void
    {
        PageView::create([
            'path' => '/writing/substrate-is-the-body',
            'is_bot' => false,
            'referrer' => 'https://sbarron.com/writing',
            'created_at' => now(),
        ]);

        $widget = new TopReferrersTable();
        $rows = (function () { return $this->buildQuery(); })->call($widget)->get();

        $this->assertCount(0, $rows);
    }

    public function test_null_or_empty_referrer_is_excluded(): void
    {
        PageView::create(['path' => '/', 'is_bot' => false, 'created_at' => now(), 'referrer' => null]);
        PageView::create(['path' => '/', 'is_bot' => false, 'created_at' => now(), 'referrer' => '']);

        $widget = new TopReferrersTable();
        $rows = (function () { return $this->buildQuery(); })->call($widget)->get();

        $this->assertCount(0, $rows);
    }
}
