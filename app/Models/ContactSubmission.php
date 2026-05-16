<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One submission from the sbarron.com engage form.
 *
 * Persisted before the notification email is sent so the lead survives
 * even if SMTP fails. `emailed` + `email_error` columns let an admin
 * see at a glance whether Shane was actually notified.
 */
class ContactSubmission extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'ip',
        'user_agent',
        'page',
        'emailed',
        'email_error',
    ];

    protected $casts = [
        'emailed' => 'boolean',
    ];
}
