<?php

namespace Tests\Unit\Support;

use App\Support\BotDetector;
use Tests\TestCase;

/**
 * Behavior tests for BotDetector.
 *
 * Contract:
 *   - looksLikeBot: true for known crawler UA substrings and for an
 *     empty/missing UA; false for ordinary browser UAs.
 *   - hashIp: null for empty IP; otherwise a stable 64-char sha256
 *     hex digest, and the same IP always hashes to the same value.
 */
class BotDetectorTest extends TestCase
{
    public function test_known_crawler_user_agents_are_bots(): void
    {
        $this->assertTrue(BotDetector::looksLikeBot('Googlebot/2.1 (+http://www.google.com/bot.html)'));
        $this->assertTrue(BotDetector::looksLikeBot('Mozilla/5.0 (compatible; bingbot/2.0)'));
        $this->assertTrue(BotDetector::looksLikeBot('facebookexternalhit/1.1'));
        $this->assertTrue(BotDetector::looksLikeBot('curl/8.4.0'));
        $this->assertTrue(BotDetector::looksLikeBot('python-requests/2.31.0'));
    }

    public function test_empty_or_missing_user_agent_is_a_bot(): void
    {
        $this->assertTrue(BotDetector::looksLikeBot(''));
        $this->assertTrue(BotDetector::looksLikeBot(null));
    }

    public function test_ordinary_browser_user_agent_is_not_a_bot(): void
    {
        $chrome = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 '
            . '(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
        $this->assertFalse(BotDetector::looksLikeBot($chrome));

        $iphone = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) '
            . 'AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1';
        $this->assertFalse(BotDetector::looksLikeBot($iphone));
    }

    public function test_hash_ip_returns_null_for_empty_ip(): void
    {
        $this->assertNull(BotDetector::hashIp(''));
        $this->assertNull(BotDetector::hashIp(null));
    }

    public function test_hash_ip_is_stable_and_sha256_shaped(): void
    {
        $a = BotDetector::hashIp('203.0.113.7');
        $b = BotDetector::hashIp('203.0.113.7');

        $this->assertSame($a, $b, 'same IP must hash to the same value');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $a);

        $other = BotDetector::hashIp('198.51.100.2');
        $this->assertNotSame($a, $other, 'different IPs must hash differently');
    }
}
