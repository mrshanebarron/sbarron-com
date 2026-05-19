<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /build and /host are the two pages that convert visitors into clients.
 * Pin down the props they ship and the tier numbers we charge.
 */
class HostingControllerTest extends TestCase
{
    public function test_build_page_renders_with_process_phases(): void
    {
        $resp = $this->get('/build');
        $resp->assertOk();
        $page = $this->extractInertiaPage($resp->getContent());

        $this->assertEquals('Build', $page['component']);
        $this->assertIsArray($page['props']['process']);
        $this->assertCount(6, $page['props']['process']);

        $phases = collect($page['props']['process'])->pluck('phase')->all();
        $this->assertEquals(
            ['Analyze', 'Spec', 'Build', 'Verify', 'Audit', 'Deploy'],
            $phases,
        );
    }

    public function test_host_page_renders_two_tiers_at_known_prices(): void
    {
        $resp = $this->get('/host');
        $resp->assertOk();
        $page = $this->extractInertiaPage($resp->getContent());

        $this->assertEquals('Host', $page['component']);
        $tiers = collect($page['props']['tiers']);

        $this->assertCount(2, $tiers);
        $this->assertEquals(20, $tiers->firstWhere('slug', 'basic')['price_monthly']);
        $this->assertEquals(40, $tiers->firstWhere('slug', 'pro')['price_monthly']);
    }

    public function test_host_page_ships_pricing_philosophy(): void
    {
        $resp = $this->get('/host');
        $page = $this->extractInertiaPage($resp->getContent());

        $included = collect($page['props']['whats_included']);
        $this->assertGreaterThan(0, $included->count());
        $this->assertTrue(
            $included->contains(fn ($line) => str_contains($line, 'no surprise renewal') || str_contains($line, 'No surprise renewal')),
            'Pricing philosophy must mention no-surprise renewal pricing',
        );
    }

    private function extractInertiaPage(string $html): array
    {
        preg_match('/data-page="app" type="application\/json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m, 'Inertia page JSON not found in response');
        return json_decode(html_entity_decode($m[1]), true);
    }
}
