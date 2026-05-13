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
        ]);
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
