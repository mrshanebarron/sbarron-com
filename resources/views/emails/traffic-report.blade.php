@php
$cellColor = function(int $count, int $max): string {
    if ($max === 0 || $count === 0) return '#1a1f2e';
    $pct = min(1.0, $count / max(1, $max));
    $light = (int) round(15 + 35 * $pct);
    $sat = (int) round(20 + 60 * $pct);
    return "hsl(40,{$sat}%,{$light}%)";
};
@endphp
<!DOCTYPE html>
<html><head><meta charset="utf-8">
<style>
 body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#0a0a0d;color:#e2e8f0;margin:0;padding:24px;font-size:14px;line-height:1.5}
 .wrap{max-width:860px;margin:0 auto}
 h1{font-size:18px;margin:0 0 4px;color:#f5d590}
 .sub{color:#a0aec0;font-size:12px;margin:0 0 24px}
 h2{font-size:13px;text-transform:uppercase;letter-spacing:.06em;color:#f5d590;border-bottom:1px solid #2d3748;padding-bottom:6px;margin:28px 0 12px}
 table{width:100%;border-collapse:collapse;font-size:13px}
 td,th{padding:4px 8px;border-bottom:1px solid #1f2530;text-align:left;vertical-align:top}
 th{color:#a0aec0;font-weight:500;font-size:11px;text-transform:uppercase;letter-spacing:.04em}
 td.num{text-align:right;font-variant-numeric:tabular-nums;font-family:Menlo,monospace}
 .pill{display:inline-block;padding:2px 8px;border-radius:10px;background:#1f2530;color:#cbd5e0;font-size:11px;margin-right:6px}
 .good{color:#86efac}.warn{color:#fbbf24}.bad{color:#f87171}
 .kv{display:grid;grid-template-columns:160px 1fr;gap:4px 14px}
 .kv b{color:#a0aec0;font-weight:500}
 code{font-family:Menlo,monospace;font-size:12px;background:#1f2530;padding:1px 6px;border-radius:3px}
 .small{color:#718096;font-size:12px}
 .heatmap{border-collapse:separate;border-spacing:1px;background:#0d1018;width:100%}
 .heatmap td{padding:0;width:3.2%;height:18px;border:none;text-align:center;font-size:10px;color:rgba(255,255,255,.4);font-family:Menlo,monospace}
 .heatmap td.label{width:auto;text-align:right;padding-right:8px;color:#a0aec0;font-family:inherit;font-size:11px;background:transparent}
 .heatmap td.hour{background:transparent;color:#718096;height:14px}
 .legend{font-size:11px;color:#718096;margin-top:6px}
 .legend span{display:inline-block;width:14px;height:14px;vertical-align:middle;margin:0 2px;border-radius:2px}
</style></head><body><div class="wrap">

<h1>{{ $r['site'] }} — {{ $r['window_label'] }}</h1>
<p class="sub">Generated {{ $r['generated_at'] }} · since {{ $r['window_since'] }}</p>

<h2>Summary</h2>
<div class="kv">
 <b>Requests</b><span>{{ number_format($r['requests']) }}</span>
 <b>Unique visitors</b><span>{{ number_format($r['unique_visitors']) }}</span>
 <b>Bytes out</b><span>{{ number_format($r['bytes_out']) }}</span>
 <b>Host load</b><span class="small">{{ $r['host']['loadavg'] ?? '—' }}</span>
 <b>Mem used</b><span class="small">{{ $r['host']['mem_used_pct'] ?? '—' }}%</span>
 <b>Disk used</b><span class="small">{{ $r['host']['disk_used_pct'] ?? '—' }}%</span>
</div>

<h2>Heatmap — 7 days × 24 hours</h2>
<table class="heatmap">
 <tr><td class="label"></td>
 @for ($h = 0; $h < 24; $h++)<td class="hour">{{ $h }}</td>@endfor
 </tr>
 @foreach ($r['heatmap']['grid'] as $day => $hours)
  <tr><td class="label">{{ $day }}</td>
  @foreach ($hours as $hour => $count)
   <td style="background:{{ $cellColor($count, $r['heatmap']['max']) }}" title="{{ $count }} reqs">{{ $count > 0 ? $count : '' }}</td>
  @endforeach
  </tr>
 @endforeach
</table>
<p class="legend">low <span style="background:#1a1f2e"></span><span style="background:hsl(40,40%,25%)"></span><span style="background:hsl(40,60%,35%)"></span><span style="background:hsl(40,80%,50%)"></span> high · max = {{ $r['heatmap']['max'] }}/hr</p>

@if (!empty($r['top_paths']))
<h2>Top paths</h2>
<table><thead><tr><th>path</th><th class="num">hits</th></tr></thead><tbody>
@foreach ($r['top_paths'] as $path => $count)
 <tr><td><code>{{ $path }}</code></td><td class="num">{{ number_format($count) }}</td></tr>
@endforeach
</tbody></table>
@endif

@if (!empty($r['status_codes']))
<h2>Status codes</h2>
<div>
@foreach ($r['status_codes'] as $code => $count)
 <span class="pill @if((int)$code>=500){{ 'bad' }}@elseif((int)$code>=400){{ 'warn' }}@elseif((int)$code>=200&&(int)$code<300){{ 'good' }}@endif">{{ $code }} · {{ number_format($count) }}</span>
@endforeach
</div>
@endif

@if (!empty($r['top_user_agents']))
<h2>Top user agents</h2>
<table><thead><tr><th>ua</th><th class="num">hits</th></tr></thead><tbody>
@foreach ($r['top_user_agents'] as $ua => $count)
 <tr><td>{{ $ua }}</td><td class="num">{{ number_format($count) }}</td></tr>
@endforeach
</tbody></table>
@endif

@if (!empty($r['errors']))
<h2>Errors (4xx + 5xx)</h2>
<table><thead><tr><th>status · method · path</th><th class="num">hits</th></tr></thead><tbody>
@foreach ($r['errors'] as $key => $count)
 <tr><td><code>{{ $key }}</code></td><td class="num">{{ number_format($count) }}</td></tr>
@endforeach
</tbody></table>
@endif

</div></body></html>
