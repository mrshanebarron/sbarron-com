<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /writing index lists pieces. /writing/{slug} renders markdown to HTML.
 * Pin down: both essay + whitepaper are listed; show route returns 404
 * for unknown slugs; rendered HTML contains expected anchor content.
 */
class WritingControllerTest extends TestCase
{
    public function test_writing_index_lists_essay_and_whitepaper(): void
    {
        $resp = $this->get('/writing');
        $resp->assertOk();
        $page = $this->extractInertiaPage($resp->getContent());

        $this->assertEquals('Writing/Index', $page['component']);
        $slugs = collect($page['props']['pieces'])->pluck('slug')->all();

        $this->assertContains('substrate-is-the-agent', $slugs);
        $this->assertContains('substrate-is-the-body', $slugs);
    }

    public function test_show_renders_essay_with_html_body(): void
    {
        $resp = $this->get('/writing/substrate-is-the-agent');
        $resp->assertOk();
        $page = $this->extractInertiaPage($resp->getContent());

        $this->assertEquals('Writing/Show', $page['component']);
        $this->assertEquals('substrate-is-the-agent', $page['props']['piece']['slug']);
        $this->assertStringContainsString('<h2', $page['props']['html']);
    }

    public function test_show_404s_on_unknown_slug(): void
    {
        $this->get('/writing/does-not-exist')->assertNotFound();
    }

    public function test_essay_metadata_is_complete(): void
    {
        $resp = $this->get('/writing');
        $page = $this->extractInertiaPage($resp->getContent());

        $essay = collect($page['props']['pieces'])->firstWhere('slug', 'substrate-is-the-agent');
        $this->assertNotNull($essay);
        $this->assertEquals('Essay', $essay['kind']);
        $this->assertNotEmpty($essay['authors']);
        $this->assertNotEmpty($essay['date']);
        $this->assertNotEmpty($essay['reading_time']);
    }

    private function extractInertiaPage(string $html): array
    {
        preg_match('/data-page="app" type="application\/json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m);
        return json_decode(html_entity_decode($m[1]), true);
    }
}
