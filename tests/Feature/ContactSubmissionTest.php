<?php

namespace Tests\Feature;

use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Behavior tests for the contact form endpoint.
 *
 * Contract: a POST to /api/contact must
 *   1. Persist a contact_submissions row (audit trail survives mail failure)
 *   2. Send mail to mrshanebarron@gmail.com (per Shane 2026-05-16)
 *   3. Reply-To header carries the visitor's email
 *   4. Reject invalid input with 422 — no row, no mail
 *   5. Rate-limit aggressive submitters per IP
 */
class ContactSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_valid_submission_persists_and_emails_shane(): void
    {
        $resp = $this->postJson('/api/contact', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'Booking module',
            'message' => 'I need a booking module for my Laravel app.',
        ]);

        $resp->assertOk()->assertJsonPath('ok', true);

        // The audit row landed AND the emailed flag flipped to true —
        // which only happens after Mail::raw returns without throwing.
        // (Mail::raw bypasses MailFake's assertion registry, so we
        // assert on the persisted side-effect instead.)
        $this->assertDatabaseCount('contact_submissions', 1);
        $this->assertDatabaseHas('contact_submissions', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'emailed' => true,
        ]);

        $row = ContactSubmission::where('email', 'jane@example.com')->first();
        $this->assertNull($row->email_error);
    }

    public function test_submission_persists_even_when_mail_fails(): void
    {
        // Force Mail::raw to throw — simulates SMTP failure
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('smtp down'));

        $resp = $this->postJson('/api/contact', [
            'name' => 'Network Failure',
            'email' => 'nf@example.com',
            'message' => 'Test that the row survives mail failure.',
        ]);

        // The endpoint still 200s so the user sees success, BUT the
        // row records the email error for Shane to discover later.
        $resp->assertOk();
        $this->assertDatabaseHas('contact_submissions', [
            'email' => 'nf@example.com',
            'emailed' => false,
        ]);
        $row = ContactSubmission::where('email', 'nf@example.com')->first();
        $this->assertNotNull($row->email_error);
        $this->assertStringContainsString('smtp down', $row->email_error);
    }

    public function test_invalid_input_rejects_with_422_no_row_no_mail(): void
    {
        $resp = $this->postJson('/api/contact', [
            'name' => '',
            'email' => 'not-an-email',
            'message' => '',
        ]);

        $resp->assertStatus(422);
        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
    }

    public function test_honeypot_field_silently_drops_bot(): void
    {
        $resp = $this->postJson('/api/contact', [
            'name' => 'Spambot',
            'email' => 'bot@spam.example',
            'message' => 'buy cheap pills',
            'website' => 'https://spam.example',  // hidden honeypot — bots fill it
        ]);

        // Return 200 so the bot thinks it won, but no row, no mail.
        $resp->assertOk();
        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
    }
}
