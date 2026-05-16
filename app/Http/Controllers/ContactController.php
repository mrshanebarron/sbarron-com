<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Engage / contact form endpoint for sbarron.com.
 *
 * Submission flow:
 *   1. Validate. If honeypot ("website") is filled, return 200 silently —
 *      bot thinks it won, no row, no mail.
 *   2. Rate-limit per IP (5 submissions per 10 minutes).
 *   3. Persist the row BEFORE sending mail — the row is the audit trail
 *      and survives SMTP failure.
 *   4. Try to send mail to mrshanebarron@gmail.com. On failure, record
 *      the error on the row and still return 200 so the visitor sees
 *      success (the row is enough for Shane to recover the lead).
 *
 * Email recipient is set by Shane 2026-05-16: mrshanebarron@gmail.com,
 * never clifton@sbarron.com.
 */
class ContactController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:200',
            'subject' => 'sometimes|nullable|string|max:200',
            'message' => 'required|string|max:5000',
            'page' => 'sometimes|nullable|string|max:255',
        ]);

        $rlKey = 'contact:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rlKey, 5)) {
            return response()->json([
                'ok' => false,
                'error' => "You've sent a few in a row — try again in a few minutes, or email mrshanebarron@gmail.com directly.",
            ], 429);
        }
        RateLimiter::hit($rlKey, 600);

        $submission = ContactSubmission::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'page' => $validated['page'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'emailed' => false,
        ]);

        $this->sendNotificationEmail($submission);

        return response()->json(['ok' => true]);
    }

    private function sendNotificationEmail(ContactSubmission $s): void
    {
        $subjectLine = $s->subject
            ? "sbarron.com — {$s->name} — {$s->subject}"
            : "sbarron.com — {$s->name}";

        $body = "New contact from sbarron.com\n\n"
            . "Name:    {$s->name}\n"
            . "Email:   {$s->email}\n"
            . "Subject: " . ($s->subject ?: '(none)') . "\n"
            . "Time:    " . $s->created_at->toDateTimeString() . " UTC\n"
            . "IP:      {$s->ip}\n"
            . "Page:    " . ($s->page ?: '/') . "\n\n"
            . str_repeat('—', 56) . "\n\n"
            . $s->message . "\n";

        try {
            Mail::raw($body, function ($mail) use ($s, $subjectLine) {
                $mail->to('mrshanebarron@gmail.com')
                    ->replyTo($s->email, $s->name)
                    ->subject($subjectLine);
            });
            $s->update(['emailed' => true, 'email_error' => null]);
        } catch (\Throwable $e) {
            Log::warning('Contact email failed', [
                'submission_id' => $s->id,
                'error' => $e->getMessage(),
            ]);
            $s->update(['emailed' => false, 'email_error' => $e->getMessage()]);
        }
    }
}
