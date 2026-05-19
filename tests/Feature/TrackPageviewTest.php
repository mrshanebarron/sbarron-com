<?php

namespace Tests\Feature;

use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behavior tests for the TrackPageview middleware.
 *
 * Contract: TrackPageview must
 *   1. Record one row per GET request to a public page
 *   2. NOT record /admin requests (Filament panel is internal)
 *   3. NOT record asset paths (/build/*, /storage/*, /favicon.ico, /robots.txt)
 *   4. NOT record non-GET requests
 *   5. NOT record AJAX/JSON responses (only HTML)
 *   6. Hash the IP so raw IPs never land in the table
 *   7. Mark known bot user-agents with is_bot=true
 *   8. Truncate over-long user agents and referrers to fit the column
 *
 * Note: 404s for unmatched routes are NOT tracked. Laravel renders
 * the 404 outside the web middleware group, and tracking every
 * unmatched URL would fill the table with bot probes for /wp-admin,
 * /.env, etc. Only successfully-routed GETs reach this middleware.
 */
class TrackPageviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_to_home_records_one_pageview(): void
    {
        $this->get('/')->assertSuccessful();
        $this->assertDatabaseCount('page_views', 1);
        $this->assertDatabaseHas('page_views', ['path' => '/', 'is_bot' => false]);
    }

    public function test_get_to_writing_records_path(): void
    {
        $this->get('/writing')->assertSuccessful();
        $this->assertDatabaseHas('page_views', ['path' => '/writing']);
    }

    public function test_admin_paths_are_not_tracked(): void
    {
        // Don't care about the response — just that no row is written.
        $this->get('/admin/login');
        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_asset_paths_are_not_tracked(): void
    {
        $this->get('/favicon.ico');
        $this->get('/robots.txt');
        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_post_requests_are_not_tracked(): void
    {
        $this->postJson('/api/contact', [
            'name' => 'x', 'email' => 'x@x.com', 'message' => 'no thanks',
        ]);
        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_json_responses_are_not_tracked(): void
    {
        $this->getJson('/api/telemetry/heartbeat');
        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_ip_is_hashed_not_stored_raw(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.42'])->get('/');
        $row = PageView::first();
        $this->assertNotNull($row);
        $this->assertNotSame('203.0.113.42', $row->ip_hash);
        $this->assertSame(64, strlen($row->ip_hash));  // sha256 hex
    }

    public function test_bot_user_agent_is_flagged(): void
    {
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])
             ->get('/');
        $this->assertDatabaseHas('page_views', ['path' => '/', 'is_bot' => true]);
    }

    public function test_unmatched_route_404_is_not_recorded(): void
    {
        // Laravel renders unmatched-route 404s outside the web middleware
        // group, so they bypass TrackPageview. This is intentional — see
        // the class docblock. Tracking every unmatched URL would fill
        // the table with bot probes (/wp-admin, /.env, etc).
        $this->get('/this-page-does-not-exist-yet-' . uniqid())
             ->assertNotFound();
        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_oversized_user_agent_is_truncated(): void
    {
        $huge = str_repeat('A', 1000);
        $this->withHeaders(['User-Agent' => $huge])->get('/');
        $row = PageView::first();
        $this->assertLessThanOrEqual(512, strlen($row->user_agent));
    }
}
