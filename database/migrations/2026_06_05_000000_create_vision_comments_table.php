<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reader comments on the /vision engineering docs.
 *
 * MODERATED BY DEFAULT: `approved` is false on insert, so a freshly-submitted
 * comment is NEVER rendered to other visitors until Shane (or Pneuma) approves
 * it. This is the security model — unapproved input cannot reach another
 * reader, which is what makes a public comment form safe to run even on a box
 * whose hardening is still being confirmed. Spam lands in the queue, not on
 * the page.
 *
 * `doc_slug` ties a comment to a specific doc (validated against the known
 * slug set in the controller, not trusted from the client).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_comments', function (Blueprint $table) {
            $table->id();
            $table->string('doc_slug', 120);
            $table->string('author_name', 80);
            // email is collected for moderation context only; never rendered.
            $table->string('author_email', 200)->nullable();
            $table->text('body');
            $table->boolean('approved')->default(false);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['doc_slug', 'approved', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_comments');
    }
};
