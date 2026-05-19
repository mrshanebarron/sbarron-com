<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class DomainController extends Controller
{
    private const MARKUP = 3.00;

    private const FEATURED_TLDS = [
        'com', 'net', 'org', 'co', 'io', 'ai', 'dev', 'app',
        'tech', 'design', 'studio', 'xyz', 'me',
    ];

    public function index(): Response
    {
        return Inertia::render('Domains', [
            'reference_pricing' => $this->referencePricing(),
            'markup' => self::MARKUP,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|min:1|max:63|regex:/^[a-z0-9-]+$/i',
        ]);

        $keyword = strtolower($request->input('keyword'));

        $results = Cache::remember(
            'domains.search.' . $keyword,
            now()->addMinutes(10),
            fn () => $this->callNameDotCom($keyword),
        );

        return response()->json(['results' => $results]);
    }

    private function callNameDotCom(string $keyword): array
    {
        $username = config('services.namecom.username');
        $token = config('services.namecom.token');

        if (! $username || ! $token) {
            return $this->fallbackResults($keyword);
        }

        $response = Http::withBasicAuth($username, $token)
            ->timeout(8)
            ->acceptJson()
            ->post('https://api.name.com/v4/domains:search', [
                'keyword' => $keyword,
                'tldFilter' => self::FEATURED_TLDS,
            ]);

        if (! $response->successful()) {
            return $this->fallbackResults($keyword);
        }

        return collect($response->json('results', []))
            ->filter(fn ($r) => $r['purchasable'] ?? false)
            ->map(fn ($r) => [
                'domain' => $r['domainName'],
                'tld' => $r['tld'],
                'first_year' => round($r['purchasePrice'] + self::MARKUP, 2),
                'renewal' => round($r['renewalPrice'] + self::MARKUP, 2),
                'wholesale_first' => $r['purchasePrice'],
                'wholesale_renewal' => $r['renewalPrice'],
            ])
            ->sortBy('first_year')
            ->values()
            ->all();
    }

    /**
     * Static reference pricing verified 2026-05-18 against the live
     * name.com reseller API. Used when the API is unreachable and as
     * the always-visible pricing card on the /domains page.
     */
    private function referencePricing(): array
    {
        $rows = [
            ['tld' => 'xyz',    'wholesale_first' =>  1.99, 'wholesale_renewal' => 20.99],
            ['tld' => 'design', 'wholesale_first' =>  4.99, 'wholesale_renewal' => 80.99],
            ['tld' => 'org',    'wholesale_first' =>  8.99, 'wholesale_renewal' => 21.99],
            ['tld' => 'com',    'wholesale_first' => 12.99, 'wholesale_renewal' => 19.99],
            ['tld' => 'tech',   'wholesale_first' => 13.99, 'wholesale_renewal' => 89.99],
            ['tld' => 'app',    'wholesale_first' => 14.99, 'wholesale_renewal' => 26.99],
            ['tld' => 'dev',    'wholesale_first' => 14.99, 'wholesale_renewal' => 22.99],
            ['tld' => 'net',    'wholesale_first' => 16.49, 'wholesale_renewal' => 23.99],
            ['tld' => 'co',     'wholesale_first' => 17.99, 'wholesale_renewal' => 48.99],
            ['tld' => 'studio', 'wholesale_first' => 21.99, 'wholesale_renewal' => 58.99],
            ['tld' => 'me',     'wholesale_first' => 26.99, 'wholesale_renewal' => 27.99],
            ['tld' => 'io',     'wholesale_first' => 53.99, 'wholesale_renewal' => 79.99],
            ['tld' => 'ai',     'wholesale_first' => 199.98,'wholesale_renewal' => 199.98],
        ];

        return collect($rows)
            ->map(fn ($r) => array_merge($r, [
                'first_year' => round($r['wholesale_first'] + self::MARKUP, 2),
                'renewal' => round($r['wholesale_renewal'] + self::MARKUP, 2),
            ]))
            ->all();
    }

    private function fallbackResults(string $keyword): array
    {
        return collect($this->referencePricing())
            ->map(fn ($r) => [
                'domain' => $keyword . '.' . $r['tld'],
                'tld' => $r['tld'],
                'first_year' => $r['first_year'],
                'renewal' => $r['renewal'],
                'wholesale_first' => $r['wholesale_first'],
                'wholesale_renewal' => $r['wholesale_renewal'],
            ])
            ->all();
    }
}
