<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Front door of Barron AI Solutions. The Home page is the one route
 * that converts visitors. We pin down the props it ships to Inertia.
 */
class HomeControllerTest extends TestCase
{
    public function test_home_renders_inertia_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Barron AI Solutions');
    }

    public function test_home_ships_ticker_matt_and_portfolio_props(): void
    {
        $resp = $this->get('/');
        $resp->assertOk();
        $page = $this->extractInertiaPage($resp->getContent());

        $this->assertEquals('Home', $page['component']);
        $this->assertIsArray($page['props']['ticker']);
        $this->assertNotEmpty($page['props']['matt']);
        $this->assertIsArray($page['props']['portfolio']);
        $this->assertGreaterThanOrEqual(3, count($page['props']['portfolio']));
    }

    public function test_portfolio_contains_betterorbitter_and_no_mindwell(): void
    {
        $resp = $this->get('/');
        $page = $this->extractInertiaPage($resp->getContent());
        $slugs = collect($page['props']['portfolio'])->pluck('slug')->all();

        $this->assertContains('betterorbitter', $slugs);
        $this->assertContains('tapestry', $slugs);
        $this->assertContains('restday', $slugs);
        $this->assertNotContains('mindwell', $slugs);
    }

    public function test_matt_proof_shows_the_real_tapestry_numbers(): void
    {
        $resp = $this->get('/');
        $page = $this->extractInertiaPage($resp->getContent());
        $matt = $page['props']['matt'];

        $this->assertEquals('Tapestry of Africa', $matt['client']);
        $this->assertEquals('$2,000', $matt['price']);
        $this->assertEquals('90 minutes', $matt['duration']);
        $this->assertEquals('122', $matt['tests']);
    }

    private function extractInertiaPage(string $html): array
    {
        preg_match('/data-page="app" type="application\/json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m, 'Inertia page JSON not found in response');
        return json_decode(html_entity_decode($m[1]), true);
    }
}
