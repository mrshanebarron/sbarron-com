<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contact submissions from the sbarron.com engage form.
 *
 * Every row is also dispatched as an email to mrshanebarron@gmail.com via
 * ContactController. The DB row is the audit trail — even if mail fails,
 * Shane can read the lead from the admin / from psql.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 200);
            $table->string('subject', 200)->nullable();
            $table->text('message');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('page', 255)->nullable();
            $table->boolean('emailed')->default(false);
            $table->text('email_error')->nullable();
            $table->timestamps();
            $table->index('created_at');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
