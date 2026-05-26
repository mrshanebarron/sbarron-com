<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily traffic + heatmap report. Added 2026-05-26.
Schedule::command('sbarron:traffic-report', [
    '--to=mrshanebarron@gmail.com',
    '--window=24',
    '--site=sbarron.com',
])->dailyAt('08:00')->onOneServer();
