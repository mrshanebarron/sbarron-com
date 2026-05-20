<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * page_interactions — raw click + scroll events for the click/scroll
 * heatmap. One row per event.
 *
 *   type = 'click'  : x_pct / y_pct set (position as % of page width/
 *                     viewport height, so the heatmap is resolution-
 *                     independent and overlays on any screenshot).
 *   type = 'scroll' : scroll_pct set (max scroll depth reached on the
 *                     visit, 0-100).
 *
 * Bots are flagged the same way page_views does so they can be excluded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_interactions', function (Blueprint $table) {
            $table->id();
            $table->string('path', 512)->index();
            $table->string('type', 16)->index();           // 'click' | 'scroll'
            $table->unsignedSmallInteger('x_pct')->nullable();   // 0-100, click only
            $table->unsignedSmallInteger('y_pct')->nullable();   // 0-100, click only
            $table->unsignedSmallInteger('scroll_pct')->nullable(); // 0-100, scroll only
            $table->unsignedSmallInteger('viewport_w')->nullable();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->boolean('is_bot')->default(false)->index();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_interactions');
    }
};
