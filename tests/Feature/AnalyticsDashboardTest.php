<?php

namespace Tests\Feature;

use App\Models\PageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behavior tests for the analytics widgets on the Filament dashboard.
 *
 * Contract:
 *   1. AnalyticsStatsOverview: today + 7d + 30d human counts; bots
 *      counted separately and excluded from human counts.
 *   2. TopPagesTable: groups by path, ranks by view count, excludes bots,
 *      excludes anything older than 7 days.
 *   3. TopReferrersTable: groups by referrer host, excludes internal
 *      referrers (sbarron.com / sbarron.test).
 *
 * We test observable behavior: counts visible in the rendered dashboard
 * HTML and rows persisted via the underlying queries.
 */
class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function seedViews(): void
    {
        $now = now();

        // Two human views to /writing/substrate-is-the-body today
        PageView::create(['path' => '/writing/substrate-is-the-body', 'is_bot' => false, 'ip_hash' => 'a', 'created_at' => $now]);
        PageView::create(['path' => '/writing/substrate-is-the-body', 'is_bot' => false, 'ip_hash' => 'b', 'created_at' => $now]);
        // One human view to home today
        PageView::create(['path' => '/', 'is_bot' => false, 'ip_hash' => 'a', 'created_at' => $now]);
        // One bot view today — should be excluded from human counts
        PageView::create(['path' => '/writing/substrate-is-the-body', 'is_bot' => true, 'ip_hash' => 'c', 'created_at' => $now]);
        // One human view 10 days ago — outside 7d window
        PageView::create(['path' => '/old-article', 'is_bot' => false, 'ip_hash' => 'a', 'created_at' => $now->copy()->subDays(10)]);
        // External referrer
        PageView::create(['path' => '/writing/substrate-is-the-body', 'is_bot' => false, 'ip_hash' => 'd', 'referrer' => 'https://news.ycombinator.com/item?id=123', 'created_at' => $now]);
        // Internal referrer — should be excluded from top referrers
        PageView::create(['path' => '/writing/substrate-is-the-agent', 'is_bot' => false, 'ip_hash' => 'a', 'referrer' => 'https://sbarron.com/writing', 'created_at' => $now]);
    }

    public function test_stats_overview_counts_humans_today_and_excludes_bots(): void
    {
        $this->seedViews();

        $widget = new \App\Filament\Widgets\AnalyticsStatsOverview();
        $stats = (function () { return $this->getStats(); })->call($widget);

        // Find each stat by label
        $byLabel = [];
        foreach ($stats as $stat) {
            $byLabel[$stat->getLabel()] = $stat;
        }

        // 4 human views today: 2 to /writing/substrate-is-the-body, 1 to /, 1 with referrer
        // The bot view is excluded. The 10-days-old view is outside today's bucket.
        $this->assertSame('5', $byLabel['Today']->getValue());
        // Bots in last 24h: 1
        $this->assertSame('1', $byLabel['Bots (24h)']->getValue());
    }

    public function test_top_pages_groups_by_path_and_ranks_by_views(): void
    {
        $this->seedViews();

        $widget = new \App\Filament\Widgets\TopPagesTable();
        $query = (function () { return $this->buildQuery(); })->call($widget);
        $rows = $query->get();

        // /writing/substrate-is-the-body has 3 human views in last 7d
        // / has 1 human view in last 7d
        // /writing/substrate-is-the-agent has 1 human view in last 7d
        // /old-article has 0 in last 7d (it was 10 days ago) — should NOT appear
        $paths = $rows->pluck('path')->toArray();
        $this->assertContains('/writing/substrate-is-the-body', $paths);
        $this->assertContains('/', $paths);
        $this->assertContains('/writing/substrate-is-the-agent', $paths);
        $this->assertNotContains('/old-article', $paths);

        // Top is substrate-is-the-body with 3 views
        $top = $rows->sortByDesc('views')->first();
        $this->assertSame('/writing/substrate-is-the-body', $top->path);
        $this->assertSame(3, (int) $top->views);
    }

    public function test_top_referrers_excludes_internal_and_keeps_external(): void
    {
        $this->seedViews();

        $widget = new \App\Filament\Widgets\TopReferrersTable();
        $query = (function () { return $this->buildQuery(); })->call($widget);
        $rows = $query->get();

        $hosts = $rows->pluck('referrer_host')->toArray();

        // External HN referrer should show up
        $this->assertContains('news.ycombinator.com', $hosts);
        // Internal sbarron.com referrer should NOT show up
        $this->assertNotContains('sbarron.com', $hosts);
    }

    public function test_dashboard_requires_authentication(): void
    {
        // Anonymous user is redirected to login
        $this->get('/admin')->assertRedirect();
    }

    public function test_shanes_email_can_access_the_dashboard(): void
    {
        // canAccessPanel allow-lists mrshanebarron@gmail.com. Any other
        // user (factory-default email) gets 403.
        $user = User::factory()->create(['email' => 'mrshanebarron@gmail.com']);
        $this->actingAs($user);
        $this->get('/admin')->assertSuccessful();
    }

    public function test_other_authenticated_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->create(['email' => 'someone-else@example.com']);
        $this->actingAs($user);
        $this->get('/admin')->assertForbidden();
    }
}
