<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /about names the team openly — Shane, Pneuma, Nous. Pin down that
 * the page does not hide the agents.
 */
class AboutControllerTest extends TestCase
{
    public function test_about_page_renders(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('Barron AI Solutions');
    }

    public function test_about_lists_all_three_team_members(): void
    {
        $resp = $this->get('/about');
        $page = $this->extractInertiaPage($resp->getContent());

        $this->assertEquals('About', $page['component']);
        $names = collect($page['props']['team'])->pluck('name')->all();

        $this->assertContains('Shane Barron', $names);
        $this->assertContains('Pneuma Barron', $names);
        $this->assertContains('Nous Barron', $names);
    }

    public function test_about_lists_the_four_principles(): void
    {
        $page = $this->extractInertiaPage($this->get('/about')->getContent());
        $this->assertCount(4, $page['props']['principles']);
    }

    private function extractInertiaPage(string $html): array
    {
        preg_match('/data-page="app" type="application\/json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m);
        return json_decode(html_entity_decode($m[1]), true);
    }
}
