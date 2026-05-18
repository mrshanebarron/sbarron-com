<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /portfolio is the full grid (live clients + MVPs). It must inherit
 * the same source-of-truth client data as the homepage (no drift).
 */
class PortfolioControllerTest extends TestCase
{
    public function test_portfolio_page_renders_with_clients_and_mvps(): void
    {
        $resp = $this->get('/portfolio');
        $resp->assertOk();
        $page = $this->extractInertiaPage($resp->getContent());

        $this->assertEquals('Portfolio', $page['component']);
        $this->assertIsArray($page['props']['clients']);
        $this->assertIsArray($page['props']['mvps']);
    }

    public function test_portfolio_clients_match_homepage_clients(): void
    {
        $homePage = $this->extractInertiaPage($this->get('/')->getContent());
        $portfolioPage = $this->extractInertiaPage($this->get('/portfolio')->getContent());

        $homeClients = collect($homePage['props']['clients'])->pluck('slug')->sort()->values()->all();
        $portfolioClients = collect($portfolioPage['props']['clients'])->pluck('slug')->sort()->values()->all();

        $this->assertEquals($homeClients, $portfolioClients);
    }

    public function test_portfolio_includes_at_least_fifteen_mvps(): void
    {
        $page = $this->extractInertiaPage($this->get('/portfolio')->getContent());
        $this->assertGreaterThanOrEqual(15, count($page['props']['mvps']));
    }

    private function extractInertiaPage(string $html): array
    {
        preg_match('/data-page="app" type="application\/json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m);
        return json_decode(html_entity_decode($m[1]), true);
    }
}
