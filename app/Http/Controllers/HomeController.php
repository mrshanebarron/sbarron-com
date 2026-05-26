<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Home', [
            'ticker' => $this->seedTicker(),
            'matt' => $this->lastShipped(),
            'portfolio' => $this->portfolio(),
            'clients' => $this->liveClients(),
            'mvps' => $this->mvpShowcase(),
        ]);
    }

    /**
     * Five live, in-production engagements. Featured in the portfolio
     * row with their own treatment — paying clients, real domains.
     * Image paths live under /public/portfolio/ and are served directly.
     *
     * @return array
     */
    private function liveClients(): array
    {
        return [
            [
                'slug' => 'tapestry',
                'name' => 'Tapestry of Africa',
                'kind' => 'Safari CRM',
                'category' => 'saas',
                'summary' => "Booking, billing, client portal, Klaviyo, Square deposits, and a full quote pipeline — running for a Kenya/Tanzania safari operator.",
                'url' => 'https://tapestryofafrica.com',
                'image' => '/portfolio/port-tapestry.png',
            ],
            [
                'slug' => 'easyquit',
                'name' => 'EasyQuit',
                'kind' => 'Telehealth',
                'category' => 'saas',
                'summary' => 'Australian quit-smoking telehealth — Stripe ID verification, e-script issuance, same-day GP bookings.',
                'url' => 'https://easyquit.com.au',
                'image' => '/portfolio/port-easyquit.png',
            ],
            [
                'slug' => 'betterorbitter',
                'name' => 'Better or Bitter',
                'kind' => 'Book community',
                'category' => 'editorial',
                'summary' => "Community site for Steve Golliher's memoir — stories of renewal, member submissions, custom WordPress theme.",
                'url' => 'https://betterorbitter.life',
                'image' => '/portfolio/port-bob.png',
            ],
            [
                'slug' => 'sunshine',
                'name' => 'Sunshine Senior Concierge',
                'kind' => 'Service business',
                'category' => 'services',
                'summary' => 'Trusted in-home support for seniors — three custom plugins replacing four off-the-shelf ones, lighter and faster for the operator.',
                'url' => 'https://sunshineseniorconcierge.com',
                'image' => '/portfolio/port-sunshine.png',
            ],
            [
                'slug' => 'restday',
                'name' => 'Rest Day Kitchen',
                'kind' => 'Editorial blog',
                'category' => 'editorial',
                'summary' => 'High-protein recipe blog with a custom Kadence-replacing theme and Kit newsletter integration.',
                'url' => 'https://restdaykitchen.com',
                'image' => '/portfolio/port-restday.png',
            ],
        ];
    }

    /**
     * Twenty selected MVP showcases — hand-picked from the demo server
     * after visual review. Each is a working site at *.mvp.sbarron.com
     * built end-to-end in a single sitting.
     *
     * Categories used for the filterable grid:
     *   ai        — working AI products (intake, chat, generators)
     *   saas      — SaaS landing + dashboard
     *   services  — professional services / B2B advisory
     *   trades    — home services / construction / install
     *   editorial — content, books, design, fashion
     *   ecom      — direct-to-consumer + marketplaces
     *
     * @return array
     */
    private function mvpShowcase(): array
    {
        return [
            ['slug' => 'intakeai',     'name' => 'IntakeAI',                'kind' => 'AI document → JSON',           'category' => 'ai',        'summary' => 'Working AI extraction. Paste any document, get clean structured data back.',                              'url' => 'https://intakeai.mvp.sbarron.com',         'image' => '/portfolio/port-intakeai.png'],
            ['slug' => 'lextriage',    'name' => 'LexTriage',               'kind' => 'Legal AI intake',              'category' => 'ai',        'summary' => 'Working chat that routes legal questions to the right attorney at a firm — preliminary intake assistant.', 'url' => 'https://lextriage-chat.mvp.sbarron.com',   'image' => '/portfolio/port-lextriage.png'],
            ['slug' => 'mylondon',     'name' => 'MyLondonTrip',            'kind' => 'AI travel itineraries',        'category' => 'ai',        'summary' => 'AI-built London plans that respect opening hours, Tube routes, and which markets are closed on Sundays.',  'url' => 'https://mylondon-trip.mvp.sbarron.com',    'image' => '/portfolio/port-mylondon.png'],
            ['slug' => 'rehab',        'name' => 'RehabScheduler',          'kind' => 'Inpatient therapy SaaS',       'category' => 'saas',      'summary' => 'Director console for inpatient therapy units — patient risk, therapist load, session board.',              'url' => 'https://rehab-scheduler.mvp.sbarron.com',  'image' => '/portfolio/port-rehab.png'],
            ['slug' => 'nimbus',       'name' => 'Nimbus',                  'kind' => 'AI lead generation',           'category' => 'saas',      'summary' => 'Pipeline you can trust — finds, scores, and warms prospects across LinkedIn and the open web.',           'url' => 'https://nimbus.mvp.sbarron.com',           'image' => '/portfolio/port-nimbus.png'],
            ['slug' => 'salesprospect','name' => 'SalesProspect',           'kind' => 'LinkedIn → mobile numbers',    'category' => 'saas',      'summary' => 'Credit-based enrichment. LinkedIn URLs in, verified names and mobile numbers out.',                        'url' => 'https://salesprospect.mvp.sbarron.com',    'image' => '/portfolio/port-salesprospect.png'],
            ['slug' => 'haven',        'name' => 'Haven',                   'kind' => 'Property management SaaS',     'category' => 'saas',      'summary' => 'Operations for homes that never compromise — every detail accounted for, in real time.',                  'url' => 'https://haven.mvp.sbarron.com',            'image' => '/portfolio/port-haven.png'],
            ['slug' => 'alboran',      'name' => 'Alboran Tax Partners',    'kind' => 'Expat tax advisory',           'category' => 'services',  'summary' => 'Spanish tax for English-speaking expats — Hacienda rules in your language and ours.',                     'url' => 'https://alboran-tax.mvp.sbarron.com',      'image' => '/portfolio/port-alboran-tax.png'],
            ['slug' => 'dataxcapital', 'name' => 'Data X Capital Partners', 'kind' => 'Boutique strategic advisory',  'category' => 'services',  'summary' => 'Clarity in complex capital decisions. Independent, senior-led, cross-border.',                            'url' => 'https://dataxcapital.mvp.sbarron.com',     'image' => '/portfolio/port-dataxcapital.png'],
            ['slug' => 'goldcompass',  'name' => 'Gold Compass Commerce',   'kind' => 'eCommerce growth agency',      'category' => 'services',  'summary' => 'Growth that compounds, not growth that just spends — paid media, lifecycle, retention.',                  'url' => 'https://gold-compass.mvp.sbarron.com',     'image' => '/portfolio/port-gold-compass.png'],
            ['slug' => 'northpine',    'name' => 'North & Pine',            'kind' => 'Small-business services firm', 'category' => 'services',  'summary' => 'A services firm for small and mid-sized businesses — work, services, clients.',                           'url' => 'https://northpine.mvp.sbarron.com',        'image' => '/portfolio/port-northpine.png'],
            ['slug' => 'fivestarbath', 'name' => '5 Star Bath & Kitchen',   'kind' => 'Bathroom & kitchen remodel',   'category' => 'trades',    'summary' => 'The bath you deserve. Built to last a lifetime. 640+ baths completed, 12 years in business.',              'url' => 'https://fivestarbath.mvp.sbarron.com',     'image' => '/portfolio/port-fivestarbath.png'],
            ['slug' => 'apex',         'name' => 'Apex Pickle Works',       'kind' => 'Pickleball court installer',   'category' => 'trades',    'summary' => 'Premium residential pickleball courts — built by the crew that installs them, finished to spec.',         'url' => 'https://apex.mvp.sbarron.com',             'image' => '/portfolio/port-apex.png'],
            ['slug' => 'granite',      'name' => 'Granite & Grade',         'kind' => 'Quarry products distributor',  'category' => 'trades',    'summary' => 'From our pit to your job site — crushed stone, gravel, sand, and decorative blends, delivered.',          'url' => 'https://granite.mvp.sbarron.com',          'image' => '/portfolio/port-granite.png'],
            ['slug' => 'fishingcharter','name' => 'Carolina Coast Charters','kind' => 'Fishing charters',             'category' => 'trades',    'summary' => 'Inshore, nearshore, and offshore charters captained by locals who fished these waters before GPS.',       'url' => 'https://fishingcharter.mvp.sbarron.com',   'image' => '/portfolio/port-fishingcharter.png'],
            ['slug' => 'howardsteinberg','name'=> 'Howard Steinberg',       'kind' => 'Author / memoir',              'category' => 'editorial', 'summary' => "Editorial book site for Confessions of a Problem Seeker — a memoir of authenticity, healing, and stillness.", 'url' => 'https://howardsteinberg.mvp.sbarron.com', 'image' => '/portfolio/port-howardsteinberg.png'],
            ['slug' => 'stratum',      'name' => 'Stratum',                 'kind' => 'Architecture practice',        'category' => 'editorial', 'summary' => 'Buildings that breathe with the land they sit on — Charleston, SC.',                                       'url' => 'https://stratum.mvp.sbarron.com',          'image' => '/portfolio/port-stratum.png'],
            ['slug' => 'shop',         'name' => 'North Atlantic Supply',   'kind' => 'eCommerce · general store',    'category' => 'ecom',      'summary' => 'Honest goods for everyday use — apparel, accessories, objects, print. Shipped from the North Atlantic coast.', 'url' => 'https://shop.mvp.sbarron.com',          'image' => '/portfolio/port-shop.png'],
            ['slug' => 'laurelridge',  'name' => 'Laurel Ridge Realty',     'kind' => 'Boutique real estate',         'category' => 'services',  'summary' => 'A home is a long quiet conversation with the land it sits on — Hudson Valley & Catskills brokerage.',     'url' => 'https://laurelridge.mvp.sbarron.com',      'image' => '/portfolio/port-laurelridge.png'],
        ];
    }

    private function seedTicker(): array
    {
        $now = now();
        return [
            ['at' => $now->copy()->subSeconds(8)->toIso8601String(),  'kind' => 'commit', 'text' => 'feat: quote pipeline · 14 files'],
            ['at' => $now->copy()->subSeconds(34)->toIso8601String(), 'kind' => 'test',   'text' => 'phpunit · 122 passed (258 assertions)'],
            ['at' => $now->copy()->subMinutes(2)->toIso8601String(),  'kind' => 'deploy', 'text' => 'mindwell.app · v1.4.0 → live'],
            ['at' => $now->copy()->subMinutes(7)->toIso8601String(),  'kind' => 'commit', 'text' => 'fix: stripe webhook idempotency on retry'],
            ['at' => $now->copy()->subMinutes(18)->toIso8601String(), 'kind' => 'vital',  'text' => 'organism.heartbeat — nominal'],
        ];
    }

    private function lastShipped(): array
    {
        return [
            'client' => 'A travel CRM',
            'title' => 'A complete quote pipeline — request → digital itinerary → booked tour with deposit — built and tested in a single sitting.',
            'scope_label' => 'CRM module + public itinerary + payments',
            'market_price' => '$15k–$25k',
            'price' => '$2,000',
            'duration' => '90 minutes',
            'commits' => '12',
            'tests' => '122',
            'assertions' => '258',
        ];
    }

    private function portfolio(): array
    {
        return [
            [
                'slug' => 'travel-crm',
                'kind' => 'CRM · Module',
                'client' => 'A travel CRM',
                'summary' => 'Booking, billing, client portal, and a full quote pipeline — running in production.',
                'live' => true,
                'built_in' => '90 min (latest module)',
                'last_deploy' => '14 hours ago',
                'stack' => 'Laravel · Livewire · MySQL',
                'status' => 'in production',
            ],
            [
                'slug' => 'betterorbitter',
                'kind' => 'Community · WordPress',
                'client' => 'Better or Bitter',
                'summary' => 'Book community for Steve Golliher\'s "Better or Bitter: A Practical Guide to Finishing Well" — stories of renewal over resentment, custom theme, member story submissions.',
                'live' => true,
                'built_in' => 'one focused build',
                'last_deploy' => 'recent',
                'stack' => 'WordPress · custom theme · DO',
                'status' => 'in production',
            ],
            [
                'slug' => 'easyquit',
                'kind' => 'Telehealth · Laravel',
                'client' => 'EasyQuit',
                'summary' => 'Australian quit-smoking telehealth — Stripe ID verification, e-script issuance, same-day GP bookings. Live at easyquit.com.au.',
                'live' => true,
                'built_in' => 'production rollout',
                'last_deploy' => 'recent',
                'stack' => 'Laravel · MySQL · Stripe · DO',
                'status' => 'in production',
            ],
            [
                'slug' => 'sunshine',
                'kind' => 'Service · WordPress',
                'client' => 'Sunshine Senior Concierge',
                'summary' => 'Service-business site. Three custom plugins replacing four off-the-shelf ones.',
                'live' => true,
                'built_in' => 'one session',
                'last_deploy' => '2 weeks ago',
                'stack' => 'WordPress · 3 hand-built plugins',
                'status' => 'in production',
            ],
            [
                'slug' => 'restday',
                'kind' => 'Editorial · Theme',
                'client' => 'Rest Day Kitchen',
                'summary' => 'Custom Kadence-replacing theme + ConvertKit/Kit newsletter integration for a high-protein recipe blog.',
                'live' => true,
                'built_in' => 'overnight',
                'last_deploy' => '5 days ago',
                'stack' => 'WordPress · custom theme',
                'status' => 'in production',
            ],
        ];
    }
}
