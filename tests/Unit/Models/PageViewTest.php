<?php

namespace Tests\Unit\Models;

use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the PageView model.
 *
 * Contract: PageView persists request metadata for analytics. It must
 *   1. Allow mass-assignment of the fields the middleware writes
 *   2. Cast is_bot to a boolean
 *   3. Cast created_at to a Carbon instance
 *   4. NOT auto-manage updated_at (this is an append-only ledger)
 */
class PageViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_persist_a_pageview_with_middleware_fields(): void
    {
        PageView::create([
            'path' => '/writing/substrate-is-the-body',
            'url' => 'https://sbarron.com/writing/substrate-is-the-body',
            'referrer' => 'https://news.ycombinator.com/',
            'user_agent' => 'Mozilla/5.0 (test)',
            'ip_hash' => str_repeat('a', 64),
            'is_bot' => false,
        ]);

        $this->assertDatabaseCount('page_views', 1);
        $row = PageView::first();
        $this->assertSame('/writing/substrate-is-the-body', $row->path);
        $this->assertFalse($row->is_bot);
    }

    public function test_is_bot_is_cast_to_boolean(): void
    {
        $row = PageView::create([
            'path' => '/',
            'is_bot' => 1,
        ]);

        $this->assertIsBool($row->refresh()->is_bot);
        $this->assertTrue($row->is_bot);
    }

    public function test_created_at_is_a_carbon_instance(): void
    {
        $row = PageView::create(['path' => '/']);

        $this->assertInstanceOf(\Carbon\Carbon::class, $row->refresh()->created_at);
    }

    public function test_table_has_no_updated_at_column(): void
    {
        // page_views is append-only; the migration intentionally omits
        // updated_at. If timestamps get re-enabled by accident, this
        // test will fail because $table->timestamp('updated_at') was
        // never created.
        $row = PageView::create(['path' => '/']);
        $this->assertFalse(array_key_exists('updated_at', $row->getAttributes()));
    }
}
