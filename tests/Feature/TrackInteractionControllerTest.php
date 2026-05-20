<?php

namespace Tests\Feature;

use App\Models\PageInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behavior tests for the interaction capture endpoint (POST /api/track-interaction).
 *
 * Contract:
 *   - Accepts a batch of click/scroll events and persists one row each.
 *   - Click events store x_pct/y_pct (0-100); scroll events store
 *     scroll_pct (0-100). Out-of-range or malformed events are dropped,
 *     not fatal.
 *   - A crawler User-Agent marks every row in the batch is_bot = true.
 *   - The endpoint always returns 204 (fire-and-forget; never breaks
 *     the visitor's page).
 */
class TrackInteractionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_click_batch_persists_one_row_per_event(): void
    {
        $res = $this->postJson('/api/track-interaction', [
            'path' => '/writing',
            'viewport_w' => 1440,
            'events' => [
                ['type' => 'click', 'x_pct' => 30, 'y_pct' => 40],
                ['type' => 'click', 'x_pct' => 55, 'y_pct' => 88],
                ['type' => 'scroll', 'scroll_pct' => 72],
            ],
        ]);

        $res->assertNoContent();

        $this->assertSame(3, PageInteraction::count());
        $this->assertSame(2, PageInteraction::where('type', 'click')->count());
        $this->assertSame(1, PageInteraction::where('type', 'scroll')->count());
        $this->assertDatabaseHas('page_interactions', [
            'path' => '/writing', 'type' => 'click', 'x_pct' => 30, 'y_pct' => 40,
        ]);
        $this->assertDatabaseHas('page_interactions', [
            'path' => '/writing', 'type' => 'scroll', 'scroll_pct' => 72,
        ]);
    }

    public function test_out_of_range_events_are_dropped_not_fatal(): void
    {
        $res = $this->postJson('/api/track-interaction', [
            'path' => '/',
            'viewport_w' => 800,
            'events' => [
                ['type' => 'click', 'x_pct' => 150, 'y_pct' => 40],   // x out of range
                ['type' => 'click', 'x_pct' => 20, 'y_pct' => -5],    // y out of range
                ['type' => 'scroll', 'scroll_pct' => 999],            // scroll out of range
                ['type' => 'click', 'x_pct' => 10, 'y_pct' => 10],    // valid
            ],
        ]);

        $res->assertNoContent();
        // Only the one valid event persists.
        $this->assertSame(1, PageInteraction::count());
        $this->assertDatabaseHas('page_interactions', ['x_pct' => 10, 'y_pct' => 10]);
    }

    public function test_crawler_user_agent_marks_rows_as_bot(): void
    {
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])
            ->postJson('/api/track-interaction', [
                'path' => '/',
                'viewport_w' => 1024,
                'events' => [['type' => 'click', 'x_pct' => 5, 'y_pct' => 5]],
            ])
            ->assertNoContent();

        $this->assertSame(1, PageInteraction::where('is_bot', true)->count());
    }

    public function test_empty_or_missing_events_is_a_no_op_but_still_204(): void
    {
        $this->postJson('/api/track-interaction', [
            'path' => '/',
            'viewport_w' => 800,
            'events' => [],
        ])->assertNoContent();

        $this->postJson('/api/track-interaction', [
            'path' => '/',
        ])->assertNoContent();

        $this->assertSame(0, PageInteraction::count());
    }

    public function test_admin_and_api_paths_are_not_tracked(): void
    {
        foreach (['/admin', '/admin/chat-conversations', '/api/something'] as $path) {
            $this->postJson('/api/track-interaction', [
                'path' => $path,
                'viewport_w' => 1024,
                'events' => [['type' => 'click', 'x_pct' => 1, 'y_pct' => 1]],
            ])->assertNoContent();
        }

        $this->assertSame(0, PageInteraction::count());
    }
}
