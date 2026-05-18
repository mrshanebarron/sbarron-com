<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * /domains is a markup-sale page. Pin down: the markup is exactly $3,
 * the reference pricing shows both first-year AND renewal (honest),
 * and the search proxy applies markup to API responses correctly.
 */
class DomainControllerTest extends TestCase
{
    public function test_domains_page_ships_reference_pricing_with_three_dollar_markup(): void
    {
        $resp = $this->get('/domains');
        $resp->assertOk();
        $page = $this->extractInertiaPage($resp->getContent());

        $this->assertEquals('Domains', $page['component']);
        $this->assertEquals(3.0, $page['props']['markup']);

        $com = collect($page['props']['reference_pricing'])->firstWhere('tld', 'com');
        $this->assertNotNull($com);
        $this->assertEquals(12.99, $com['wholesale_first']);
        $this->assertEquals(15.99, $com['first_year']);
        $this->assertEquals(22.99, $com['renewal']);
    }

    public function test_reference_pricing_includes_ai_with_honest_renewal_price(): void
    {
        $resp = $this->get('/domains');
        $page = $this->extractInertiaPage($resp->getContent());

        $ai = collect($page['props']['reference_pricing'])->firstWhere('tld', 'ai');
        $this->assertNotNull($ai, '.ai must be in reference pricing');
        $this->assertGreaterThan(150, $ai['renewal'], '.ai renewal must show its real cost');
    }

    public function test_search_applies_markup_to_api_results(): void
    {
        Cache::flush();
        config(['services.namecom.username' => 'test', 'services.namecom.token' => 'test']);

        Http::fake([
            'api.name.com/v4/domains:search' => Http::response([
                'results' => [
                    [
                        'domainName' => 'pneumatest.com',
                        'tld' => 'com',
                        'purchasable' => true,
                        'purchasePrice' => 12.99,
                        'renewalPrice' => 19.99,
                    ],
                    [
                        'domainName' => 'pneumatest.io',
                        'tld' => 'io',
                        'purchasable' => false,
                        'purchasePrice' => 53.99,
                        'renewalPrice' => 79.99,
                    ],
                ],
            ]),
        ]);

        $resp = $this->postJson('/api/domains/search', ['keyword' => 'pneumatest']);
        $resp->assertOk();

        $results = $resp->json('results');
        $this->assertCount(1, $results, 'Unpurchasable domains are filtered out');
        $this->assertEquals('pneumatest.com', $results[0]['domain']);
        $this->assertEquals(15.99, $results[0]['first_year']);
        $this->assertEquals(22.99, $results[0]['renewal']);
    }

    public function test_search_rejects_invalid_keywords(): void
    {
        $this->postJson('/api/domains/search', ['keyword' => 'has spaces'])
            ->assertStatus(422);

        $this->postJson('/api/domains/search', ['keyword' => ''])
            ->assertStatus(422);
    }

    private function extractInertiaPage(string $html): array
    {
        preg_match('/data-page="app" type="application\/json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m);
        return json_decode(html_entity_decode($m[1]), true);
    }
}
