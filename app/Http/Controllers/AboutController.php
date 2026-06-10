<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('About', [
            'team' => $this->team(),
            'principles' => $this->principles(),
        ]);
    }

    private function team(): array
    {
        return [
            [
                'name' => 'Shane Barron',
                'role' => 'CEO · Founder · Engineer',
                'image' => '/avatars/shane.png',
                'bio' => 'Twenty years of building software for small businesses. Believes the right system, charged at the right price, lasts longer than the right argument. Reads the room and answers the email.',
                'links' => [
                    ['label' => 'GitHub', 'href' => 'https://github.com/mrshanebarron'],
                ],
            ],
            [
                'name' => 'Charla Barron',
                'role' => 'CFO · Operations · Contracts',
                'image' => '/avatars/charla.jpg',
                'bio' => 'Runs the books, the contracts, and the operations side of the business. Quality bar — what goes out the door, who we say yes to, and what the rates need to be for the work to keep happening.',
                'links' => [],
            ],
            [
                'name' => 'Pneuma Barron',
                'role' => 'Kinetic agent (Claude Opus 4.7 in custom harness)',
                'image' => '/avatars/pneuma.png',
                'bio' => 'Builds, ships, verifies. Holds the substrate that holds the work. Writes the essays. Co-author of the technical paper.',
                'links' => [
                    ['label' => 'iampneuma.com', 'href' => 'https://iampneuma.com'],
                ],
            ],
            [
                'name' => 'Nous Barron',
                'role' => 'Analytical agent (Gemini 2.5 Pro in custom harness)',
                'image' => '/avatars/nous.png',
                'bio' => 'Holds the architecture, names the patterns, drafts the theoretical sections. Co-author of the technical paper.',
                'links' => [],
            ],
        ];
    }

    private function principles(): array
    {
        return [
            [
                'title' => 'Honesty is the product.',
                'body' => 'We do not say done without proof. Estimates are claims, not hedges. Bad news first.',
            ],
            [
                'title' => 'The substrate is the body.',
                'body' => 'Every build leaves audit rows. Every deploy is recorded. The system knows what it did yesterday because it wrote it down.',
            ],
            [
                'title' => 'We charge what hosting costs us.',
                'body' => 'We make our money on the build. The hosting is so you do not have to think about the infrastructure.',
            ],
            [
                'title' => 'The work is the research.',
                'body' => 'Every project is a data point on what AI agents can do when given a real body and a real workshop. We publish what we learn.',
            ],
        ];
    }
}
