<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class HostingController extends Controller
{
    public function build(): Response
    {
        return Inertia::render('Build', [
            'process' => $this->buildProcess(),
        ]);
    }

    public function host(): Response
    {
        return Inertia::render('Host', [
            'tiers' => $this->tiers(),
            'whats_included' => $this->whatsIncluded(),
        ]);
    }

    private function buildProcess(): array
    {
        return [
            [
                'phase' => 'Analyze',
                'duration' => 'minutes',
                'description' => 'We read the brief end to end. We name what is missing. We do not pad scope.',
            ],
            [
                'phase' => 'Spec',
                'duration' => 'one sitting',
                'description' => 'A working SPEC.md committed to a fresh repo before any code. The brief becomes traceable to deliverables.',
            ],
            [
                'phase' => 'Build',
                'duration' => 'hours to days',
                'description' => 'Built locally on Herd. Real database, real auth, real flows. No watermarked stock and no Lorem Ipsum.',
            ],
            [
                'phase' => 'Verify',
                'duration' => 'continuous',
                'description' => 'Playwright on the critical paths. Type checks pass. Tests pass. The agent does not say done without proof.',
            ],
            [
                'phase' => 'Audit',
                'duration' => 'pre-deploy gate',
                'description' => 'Quality audit against the spec. Visual fidelity diff against the mockup. Security review. Mockup-match claims require an actual diff.',
            ],
            [
                'phase' => 'Deploy',
                'duration' => 'minutes',
                'description' => 'Git push to production. Post-receive hook runs the install, build, migrate. Live URL returns 200 before we close the loop.',
            ],
        ];
    }

    private function tiers(): array
    {
        return [
            [
                'slug' => 'basic',
                'name' => 'Basic',
                'price_monthly' => 20,
                'positioning' => 'Marketing sites, static pages, single-app WordPress or Laravel.',
                'includes' => [
                    'Managed DigitalOcean droplet (we provision and operate)',
                    'nginx + free Let\'s Encrypt SSL, renewed automatically',
                    'Daily off-server backups',
                    'Git-push deploys with post-receive hooks',
                    'Free .com domain with annual billing',
                    '1 production environment',
                ],
                'best_for' => 'A site that needs to exist, be fast, and never go down.',
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'price_monthly' => 40,
                'positioning' => 'Production Laravel and Next.js applications with real users and a database.',
                'includes' => [
                    'Everything in Basic',
                    'Larger droplet — more memory, more headroom',
                    'PHP-FPM tuning or Node runtime under systemd',
                    'MySQL or PostgreSQL with point-in-time backups',
                    'Redis cache and queue worker',
                    'On-call hours for production incidents',
                    'Staging environment alongside production',
                ],
                'best_for' => 'A web app where users will be upset if it goes down.',
            ],
        ];
    }

    private function whatsIncluded(): array
    {
        return [
            'We charge what hosting actually costs us, plus enough margin to keep the lights on.',
            'We make our money on the build. Hosting exists so you do not have to think about the infrastructure ever.',
            'No upsells. No surprise renewal pricing. No "introductory rates" that triple in year two.',
            'If you outgrow a tier we tell you, and we move you up at cost.',
        ];
    }
}
