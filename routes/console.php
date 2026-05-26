<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily traffic + heatmap reports. Added 2026-05-26.
// All read from the shared /var/log/nginx/access.log (with_host log_format,
// installed 2026-05-26). Filter per-site by --host.
Schedule::command('sbarron:traffic-report', [
    '--to=mrshanebarron@gmail.com',
    '--window=24',
    '--site=sbarron.com',
    '--host=sbarron.com',
])->dailyAt('08:00')->onOneServer();

Schedule::command('sbarron:traffic-report', [
    '--to=mrshanebarron@gmail.com',
    '--window=24',
    '--site=iampneuma.com',
    '--host=iampneuma.com',
])->dailyAt('08:05')->onOneServer();

// All MVP demos rolled up. No --host filter, but log only contains droplet
// traffic so report shows top_hosts with all *.mvp.sbarron.com vhosts.
Schedule::command('sbarron:traffic-report', [
    '--to=mrshanebarron@gmail.com',
    '--window=24',
    '--site=mvp.sbarron.com (all demos)',
])->dailyAt('08:10')->onOneServer();
