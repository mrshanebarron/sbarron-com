<?php

namespace Tests\Unit\Mail;

use App\Mail\TrafficReportMail;
use Tests\TestCase;

class TrafficReportMailTest extends TestCase
{
    public function test_envelope_uses_subject_line(): void
    {
        $mail = new TrafficReportMail('[sbarron] 100 reqs', '<p>body</p>');
        $envelope = $mail->envelope();

        $this->assertSame('[sbarron] 100 reqs', $envelope->subject);
    }

    public function test_content_renders_html_body(): void
    {
        $mail = new TrafficReportMail('subj', '<h1>Hello</h1>');
        $content = $mail->content();

        $this->assertStringContainsString('<h1>Hello</h1>', $content->htmlString);
    }
}
