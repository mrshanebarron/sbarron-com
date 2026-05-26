<?php

namespace App\Console\Commands;

use App\Mail\TrafficReportMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Daily traffic report for sbarron.com (also reused on iampneuma.com via
 * site flag). Parses nginx access log, renders a 7d x 24h heatmap, top
 * paths, status codes, top UAs, 4xx/5xx rollup. Emails Shane.
 *
 * Built 2026-05-26 after Shane noticed no reports were landing from any
 * site. Universal-shape version of Mneva's TrafficReportCommand — no
 * app-specific endpoints, just access log + host vitals.
 */
class TrafficReportCommand extends Command
{
    protected $signature = 'sbarron:traffic-report
                            {--to=mrshanebarron@gmail.com : Recipient}
                            {--window=24 : Hours to look back}
                            {--log=/var/log/nginx/access.log : Nginx access log path}
                            {--site=sbarron.com : Site label for subject + heading}
                            {--dry : Print HTML instead of sending}';

    protected $description = 'Email Shane a traffic + heatmap digest.';

    public function handle(): int
    {
        $windowHours = (int) $this->option('window');
        $log = $this->option('log');
        $site = $this->option('site');
        $since = now()->subHours($windowHours);

        $report = $this->parseLog($log, $since);
        $report['window_label'] = "last {$windowHours}h";
        $report['window_since'] = $since->toISO8601String();
        $report['generated_at'] = now()->toISO8601String();
        $report['site'] = $site;
        $report['host'] = $this->hostPulse();
        $report['heatmap'] = $this->buildHeatmap($report['by_hour'] ?? []);

        $html = view('emails.traffic-report', ['r' => $report])->render();
        $subject = "[{$site}] {$report['requests']} reqs · "
            . "{$report['unique_visitors']} visitors · last {$windowHours}h";

        if ($this->option('dry')) {
            $this->info("Subject: $subject\n");
            $this->line(strip_tags($html));
            return self::SUCCESS;
        }

        try {
            Mail::to($this->option('to'))->send(new TrafficReportMail($subject, $html));
            $this->info("Traffic report sent: $subject");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Send failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function parseLog(string $log, \Carbon\Carbon $since): array
    {
        if (! is_readable($log)) {
            return $this->emptyReport();
        }
        $lines = $this->tailLines($log, 200000);

        $ips = [];
        $byPath = [];
        $byStatus = [];
        $byUA = [];
        $byHour = [];
        $errors = [];
        $count = 0;
        $bytesOut = 0;

        foreach ($lines as $line) {
            if (! preg_match('/^(\S+) \S+ \S+ \[([^\]]+)\] "(\S+) (\S+) [^"]*" (\d+) (\d+) "([^"]*)" "([^"]*)"/', $line, $m)) {
                continue;
            }
            [, $ip, $when, $method, $path, $status, $bytes, $ref, $ua] = $m;
            try {
                $ts = \Carbon\Carbon::createFromFormat('d/M/Y:H:i:s O', $when);
                if (! $ts || $ts->lt($since)) continue;
            } catch (\Throwable) {
                continue;
            }

            $count++;
            $bytesOut += (int) $bytes;
            $ips[$ip] = ($ips[$ip] ?? 0) + 1;
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
            $byUA[$this->shortUA($ua)] = ($byUA[$this->shortUA($ua)] ?? 0) + 1;

            $bucket = $this->bucketPath($path);
            $byPath[$bucket] = ($byPath[$bucket] ?? 0) + 1;
            $byHour[$ts->format('Y-m-d H')] = ($byHour[$ts->format('Y-m-d H')] ?? 0) + 1;

            if ((int) $status >= 400) {
                $errKey = "$status $method $bucket";
                $errors[$errKey] = ($errors[$errKey] ?? 0) + 1;
            }
        }

        arsort($byPath);
        arsort($byStatus);
        arsort($byUA);
        arsort($errors);

        return [
            'requests' => $count,
            'unique_visitors' => count($ips),
            'bytes_out' => $bytesOut,
            'top_paths' => array_slice($byPath, 0, 15, true),
            'status_codes' => $byStatus,
            'top_user_agents' => array_slice($byUA, 0, 8, true),
            'errors' => array_slice($errors, 0, 10, true),
            'by_hour' => $byHour,
        ];
    }

    private function buildHeatmap(array $byHour): array
    {
        $now = now();
        $grid = [];
        $max = 0;
        for ($d = 6; $d >= 0; $d--) {
            $day = $now->copy()->subDays($d);
            $label = $day->format('D M-d');
            $grid[$label] = array_fill(0, 24, 0);
            for ($h = 0; $h < 24; $h++) {
                $key = $day->format('Y-m-d') . ' ' . $h;
                $count = $byHour[$key] ?? 0;
                $grid[$label][$h] = $count;
                if ($count > $max) $max = $count;
            }
        }
        return ['grid' => $grid, 'max' => $max];
    }

    private function shortUA(string $ua): string
    {
        if (preg_match('/(Chrome|Firefox|Safari|Edge|bot|spider|Googlebot|bingbot)\S*/i', $ua, $m)) {
            return $m[1];
        }
        return substr($ua, 0, 40);
    }

    private function bucketPath(string $path): string
    {
        $path = strtok($path, '?');
        $path = preg_replace('#/\d+(/|$)#', '/{id}$1', $path);
        return substr($path, 0, 60);
    }

    private function tailLines(string $path, int $maxLines): array
    {
        $size = filesize($path);
        if ($size === 0) return [];
        $fp = fopen($path, 'rb');
        $bytesToRead = min($size, $maxLines * 400);
        fseek($fp, max(0, $size - $bytesToRead));
        $buf = fread($fp, $bytesToRead);
        fclose($fp);
        $lines = explode("\n", $buf);
        if (count($lines) > 1) array_shift($lines);
        return $lines;
    }

    private function hostPulse(): array
    {
        $loadavg = trim(@file_get_contents('/proc/loadavg') ?: '');
        $meminfo = @file_get_contents('/proc/meminfo') ?: '';
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $mt);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $ma);
        $memTotalKb = (int) ($mt[1] ?? 0);
        $memAvailKb = (int) ($ma[1] ?? 0);

        $df = @shell_exec('df -BM / | tail -1');
        $diskUsedPct = null;
        if ($df && preg_match('/\s+(\d+)M\s+(\d+)M\s+(\d+)M\s+(\d+)%/', $df, $m)) {
            $diskUsedPct = (int) $m[4];
        }

        return [
            'loadavg' => $loadavg,
            'mem_used_pct' => $memTotalKb ? round(100 * (1 - $memAvailKb / $memTotalKb), 1) : null,
            'disk_used_pct' => $diskUsedPct,
        ];
    }

    private function emptyReport(): array
    {
        return [
            'requests' => 0, 'unique_visitors' => 0, 'bytes_out' => 0,
            'top_paths' => [], 'status_codes' => [], 'top_user_agents' => [],
            'errors' => [], 'by_hour' => [],
        ];
    }
}
