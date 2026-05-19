<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PortfolioController extends Controller
{
    public function __invoke(): Response
    {
        // Reuse HomeController's private fixtures as the single source
        // of truth so /portfolio and / never drift apart.
        $home = new HomeController();
        $ref = new \ReflectionClass($home);

        $liveClients = $ref->getMethod('liveClients');
        $liveClients->setAccessible(true);

        $mvpShowcase = $ref->getMethod('mvpShowcase');
        $mvpShowcase->setAccessible(true);

        return Inertia::render('Portfolio', [
            'clients' => $liveClients->invoke($home),
            'mvps' => $mvpShowcase->invoke($home),
        ]);
    }
}
