<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily traffic + heatmap reports. Added 2026-05-26.
// Two reports from the same droplet: sbarron.com (shared access.log) and
// iampneuma.com (dedicated access log added to nginx 2026-05-26).
Schedule::command('sbarron:traffic-report', [
    '--to=mrshanebarron@gmail.com',
    '--window=24',
    '--site=sbarron.com',
])->dailyAt('08:00')->onOneServer();

Schedule::command('sbarron:traffic-report', [
    '--to=mrshanebarron@gmail.com',
    '--window=24',
    '--site=iampneuma.com',
    '--log=/var/log/nginx/iampneuma.access.log',
])->dailyAt('08:05')->onOneServer();
