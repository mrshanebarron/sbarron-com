<?php

namespace Tests\Feature;

use App\Mail\TrafficReportMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Traffic report command pins observable behavior — does it dispatch
 * the right Mailable to the right recipient with a subject that names
 * the site, and does --dry skip the send.
 */
class TrafficReportCommandTest extends TestCase
{
    public function test_dry_run_does_not_send_mail(): void
    {
        Mail::fake();

        $this->artisan('sbarron:traffic-report', ['--dry' => true, '--log' => '/nonexistent'])
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_send_dispatches_traffic_report_mail(): void
    {
        Mail::fake();

        $this->artisan('sbarron:traffic-report', [
            '--to' => 'test@example.com',
            '--log' => '/nonexistent',
            '--site' => 'sbarron.com',
        ])->assertExitCode(0);

        Mail::assertSent(TrafficReportMail::class, function ($mail) {
            return $mail->hasTo('test@example.com')
                && str_contains($mail->subjectLine, 'sbarron.com');
        });
    }
}
