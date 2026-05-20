/**
 * Click + scroll capture for the admin heatmap.
 *
 * Per page visit:
 *   - every click is recorded as { x_pct, y_pct } — position as a
 *     percentage of the document width / viewport height, so the
 *     server data is resolution-independent and overlays on any
 *     screenshot.
 *   - the deepest scroll reached is recorded once, as scroll_pct.
 *
 * The batch is POSTed to /api/track-interaction when the visit ends:
 * on Inertia navigation away, and on pagehide (tab close / reload).
 * Fire-and-forget — uses sendBeacon so it survives unload.
 *
 * Admin/api paths are skipped client-side too (the server skips them
 * as well; this just avoids the request).
 */

const ENDPOINT = '/api/track-interaction'
const SKIP_PREFIXES = ['admin', 'livewire', 'filament', 'build', 'storage', '_debugbar', 'api']

let events = []
let maxScrollPct = 0
let currentPath = location.pathname
let flushed = false

function isSkipped(pathname) {
  const first = pathname.replace(/^\/+/, '').split('/')[0] || ''
  return SKIP_PREFIXES.includes(first)
}

function recordClick(e) {
  // documentElement.scrollWidth is the full page width; clientHeight is
  // the visible viewport. % keeps the point meaningful across devices.
  const docW = document.documentElement.scrollWidth || window.innerWidth || 1
  const viewH = window.innerHeight || 1
  const xPct = Math.round(((e.pageX || 0) / docW) * 100)
  const yPct = Math.round(((e.clientY || 0) / viewH) * 100)
  if (xPct < 0 || xPct > 100 || yPct < 0 || yPct > 100) return
  events.push({ type: 'click', x_pct: xPct, y_pct: yPct })
}

function recordScroll() {
  const doc = document.documentElement
  const scrollable = (doc.scrollHeight - doc.clientHeight)
  if (scrollable <= 0) {
    maxScrollPct = Math.max(maxScrollPct, 100) // page fits — counts as fully seen
    return
  }
  const pct = Math.round((window.scrollY / scrollable) * 100)
  if (pct > maxScrollPct) {
    maxScrollPct = Math.min(100, Math.max(0, pct))
  }
}

function buildPayload() {
  const batch = events.slice()
  if (maxScrollPct > 0) {
    batch.push({ type: 'scroll', scroll_pct: maxScrollPct })
  }
  return {
    path: currentPath,
    viewport_w: window.innerWidth || null,
    events: batch,
  }
}

function flush() {
  if (flushed) return
  if (isSkipped(currentPath)) { reset(); return }
  const payload = buildPayload()
  if (payload.events.length === 0) { reset(); return }

  flushed = true
  try {
    const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' })
    if (navigator.sendBeacon && navigator.sendBeacon(ENDPOINT, blob)) {
      reset()
      return
    }
  } catch {
    // fall through to fetch
  }
  // Fallback when sendBeacon is unavailable or refused the payload.
  fetch(ENDPOINT, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
    keepalive: true,
  }).catch(() => {})
  reset()
}

function reset() {
  events = []
  maxScrollPct = 0
  flushed = false
  currentPath = location.pathname
}

export function startHeatmapCapture() {
  document.addEventListener('click', recordClick, { passive: true, capture: true })
  window.addEventListener('scroll', recordScroll, { passive: true })
  window.addEventListener('pagehide', flush)

  // Inertia SPA navigation: flush the visit that is ending, then the
  // 'navigate' event starts a fresh visit on the new page.
  document.addEventListener('inertia:before', flush)
  document.addEventListener('inertia:navigate', () => {
    reset()
    recordScroll() // capture initial scroll state of the new page
  })

  recordScroll()
}
