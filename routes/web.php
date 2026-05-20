<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\TelemetryController;
use App\Http\Controllers\TrackInteractionController;
use App\Http\Controllers\WritingController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

// Pages
Route::get('/',           HomeController::class)->name('home');
Route::get('/build',      [HostingController::class, 'build'])->name('build');
Route::get('/host',       [HostingController::class, 'host'])->name('host');
Route::get('/domains',    [DomainController::class, 'index'])->name('domains');
Route::get('/portfolio',  PortfolioController::class)->name('portfolio');
Route::get('/writing',    [WritingController::class, 'index'])->name('writing.index');
Route::get('/writing/{slug}', [WritingController::class, 'show'])->name('writing.show');
Route::get('/about',      AboutController::class)->name('about');
Route::get('/contact',    fn () => Inertia::render('Contact'))->name('contact');

// APIs
Route::post('/api/chat',           ChatController::class)->name('api.chat');
Route::post('/api/chat/end',       [ChatController::class, 'end'])->name('api.chat.end');
Route::post('/api/contact',        ContactController::class)->name('api.contact');
Route::post('/api/domains/search', [DomainController::class, 'search'])->name('api.domains.search');
Route::get('/api/telemetry',       TelemetryController::class)->name('api.telemetry');
Route::post('/api/track-interaction', TrackInteractionController::class)->name('api.track-interaction');
