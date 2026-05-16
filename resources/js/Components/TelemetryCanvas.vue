<script setup>
/**
 * Telemetry canvas — fixed full-viewport background that sits behind
 * every page section. Pure 2D canvas, no WebGL. Renders:
 *
 *   1) A breathing dot matrix (cyan, very low alpha) — synchronized 4s
 *      scale+alpha pulse, reads as "the substrate is alive."
 *   2) Occasional tracer packets — straight cyan or magenta line segments
 *      that travel a short path across the viewport, leaving a fading
 *      trail. Reads as "data is moving through here."
 *
 * Performance:
 *   - DPR-aware sizing
 *   - Pauses on tab hide (visibilitychange)
 *   - Static fallback under prefers-reduced-motion
 *   - Dot count capped so a 4K monitor doesn't tank
 *   - Reuses a fixed pool of tracer slots — no per-frame allocations
 */

import { onMounted, onBeforeUnmount, ref } from 'vue'

const canvas = ref(null)

const CFG = {
  dotSpacing: 36,           // px between dots
  dotRadius: 1.0,
  dotColor: 'rgba(255, 154, 60, ',  // amber sodium — primary chrome
  dotAlphaBase: 0.10,
  dotAlphaPulse: 0.08,      // additional alpha at pulse peak
  pulsePeriod: 4000,        // ms full cycle

  tracerCount: 6,           // concurrent tracers
  tracerSpawnMin: 1200,     // ms between spawns
  tracerSpawnMax: 3800,
  tracerSpeedMin: 220,      // px/s
  tracerSpeedMax: 520,
  tracerLength: 90,         // visual tail length px
  tracerHeadRadius: 1.6,
  tracerColors: [
    'rgba(255, 154, 60,',    // amber — common (sodium street lamp)
    'rgba(255, 154, 60,',
    'rgba(79, 168, 255,',    // bridge-blue — stress accent
    'rgba(77, 214, 232,',    // cyan — rare data state
  ],
}

let ctx = null
let dpr = 1
let cssW = 0
let cssH = 0
let raf = 0
let lastTs = 0
let startTs = 0
let running = true
let dotCols = 0
let dotRows = 0
let dotXs = []
let dotYs = []
const tracers = []          // pool
let nextSpawnAt = 0

function resize() {
  cssW = window.innerWidth
  cssH = window.innerHeight
  dpr = Math.max(window.devicePixelRatio || 1, 1)
  if (!canvas.value) return
  canvas.value.width  = Math.floor(cssW * dpr)
  canvas.value.height = Math.floor(cssH * dpr)
  canvas.value.style.width  = cssW + 'px'
  canvas.value.style.height = cssH + 'px'
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0)
  rebuildDotGrid()
}

function rebuildDotGrid() {
  dotCols = Math.ceil(cssW / CFG.dotSpacing) + 1
  dotRows = Math.ceil(cssH / CFG.dotSpacing) + 1
  // Hard cap for very large displays
  const cap = 6000
  if (dotCols * dotRows > cap) {
    const scale = Math.sqrt(cap / (dotCols * dotRows))
    dotCols = Math.max(8, Math.floor(dotCols * scale))
    dotRows = Math.max(8, Math.floor(dotRows * scale))
  }
  dotXs = new Array(dotCols)
  dotYs = new Array(dotRows)
  const stepX = cssW / Math.max(dotCols - 1, 1)
  const stepY = cssH / Math.max(dotRows - 1, 1)
  for (let i = 0; i < dotCols; i++) dotXs[i] = i * stepX
  for (let j = 0; j < dotRows; j++) dotYs[j] = j * stepY
}

function spawnTracer(now) {
  // pick edge of viewport, head inward
  const edge = Math.floor(Math.random() * 4) // 0=top, 1=right, 2=bottom, 3=left
  let x, y, vx, vy
  const speed = CFG.tracerSpeedMin + Math.random() * (CFG.tracerSpeedMax - CFG.tracerSpeedMin)
  switch (edge) {
    case 0: x = Math.random() * cssW; y = -20;     vx = (Math.random() * 0.6 - 0.3) * speed; vy = speed; break
    case 1: x = cssW + 20;            y = Math.random() * cssH; vx = -speed; vy = (Math.random() * 0.6 - 0.3) * speed; break
    case 2: x = Math.random() * cssW; y = cssH+20; vx = (Math.random() * 0.6 - 0.3) * speed; vy = -speed; break
    case 3: x = -20;                  y = Math.random() * cssH; vx = speed; vy = (Math.random() * 0.6 - 0.3) * speed; break
  }
  const colorPrefix = CFG.tracerColors[Math.floor(Math.random() * CFG.tracerColors.length)]
  // Lifetime — time it takes to cross with some margin
  const lifeMs = (Math.max(cssW, cssH) / speed) * 1000 * 0.9

  // Find a free slot or push
  let slot = tracers.find(t => t.dead)
  if (!slot && tracers.length < CFG.tracerCount) {
    slot = { dead: true }
    tracers.push(slot)
  }
  if (!slot) return // pool full

  slot.dead = false
  slot.x = x; slot.y = y; slot.vx = vx; slot.vy = vy
  slot.bornAt = now
  slot.lifeMs = lifeMs
  slot.colorPrefix = colorPrefix
}

function drawDots(t) {
  // breathing pulse — sine wave over CFG.pulsePeriod
  const phase = (t % CFG.pulsePeriod) / CFG.pulsePeriod
  const pulse = (Math.sin(phase * Math.PI * 2) + 1) * 0.5  // 0..1
  const alpha = CFG.dotAlphaBase + CFG.dotAlphaPulse * pulse
  ctx.fillStyle = CFG.dotColor + alpha.toFixed(3) + ')'
  for (let i = 0; i < dotCols; i++) {
    const x = dotXs[i]
    for (let j = 0; j < dotRows; j++) {
      const y = dotYs[j]
      ctx.beginPath()
      ctx.arc(x, y, CFG.dotRadius, 0, Math.PI * 2)
      ctx.fill()
    }
  }
}

function drawTracers(t, dt) {
  for (const tr of tracers) {
    if (tr.dead) continue
    const age = t - tr.bornAt
    if (age > tr.lifeMs) { tr.dead = true; continue }
    tr.x += tr.vx * dt
    tr.y += tr.vy * dt

    // Tail
    const ux = tr.vx
    const uy = tr.vy
    const mag = Math.hypot(ux, uy) || 1
    const tailX = tr.x - (ux / mag) * CFG.tracerLength
    const tailY = tr.y - (uy / mag) * CFG.tracerLength

    const lifeT = age / tr.lifeMs
    // fade in then out — quick ramp, longer mid, fade tail
    const headAlpha = lifeT < 0.1
      ? lifeT * 10 * 0.9
      : lifeT > 0.9 ? (1 - lifeT) * 10 * 0.9 : 0.9

    const grad = ctx.createLinearGradient(tailX, tailY, tr.x, tr.y)
    grad.addColorStop(0, tr.colorPrefix + '0)')
    grad.addColorStop(1, tr.colorPrefix + (headAlpha * 0.7).toFixed(3) + ')')
    ctx.strokeStyle = grad
    ctx.lineWidth = 1.2
    ctx.beginPath()
    ctx.moveTo(tailX, tailY)
    ctx.lineTo(tr.x, tr.y)
    ctx.stroke()

    // Head dot
    ctx.fillStyle = tr.colorPrefix + headAlpha.toFixed(3) + ')'
    ctx.beginPath()
    ctx.arc(tr.x, tr.y, CFG.tracerHeadRadius, 0, Math.PI * 2)
    ctx.fill()
  }
}

function step(ts) {
  if (!running) return
  if (!startTs) startTs = ts
  const t = ts - startTs
  const dt = lastTs ? Math.min((ts - lastTs) / 1000, 0.05) : 0
  lastTs = ts

  ctx.clearRect(0, 0, cssW, cssH)
  drawDots(t)
  drawTracers(t, dt)

  if (t > nextSpawnAt) {
    spawnTracer(t)
    nextSpawnAt = t + CFG.tracerSpawnMin + Math.random() * (CFG.tracerSpawnMax - CFG.tracerSpawnMin)
  }

  raf = requestAnimationFrame(step)
}

function drawStatic() {
  ctx.clearRect(0, 0, cssW, cssH)
  ctx.fillStyle = CFG.dotColor + CFG.dotAlphaBase.toFixed(3) + ')'
  for (let i = 0; i < dotCols; i++) {
    const x = dotXs[i]
    for (let j = 0; j < dotRows; j++) {
      ctx.beginPath()
      ctx.arc(x, dotYs[j], CFG.dotRadius, 0, Math.PI * 2)
      ctx.fill()
    }
  }
}

function start() {
  running = true
  lastTs = 0
  startTs = 0
  raf = requestAnimationFrame(step)
}

function stop() {
  running = false
  if (raf) cancelAnimationFrame(raf)
}

let onResize, onVis
const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches

onMounted(() => {
  if (!canvas.value) return
  ctx = canvas.value.getContext('2d', { alpha: true })
  if (!ctx) return
  resize()
  if (reducedMotion()) {
    drawStatic()
  } else {
    start()
  }

  onResize = () => { resize(); if (reducedMotion()) drawStatic() }
  onVis = () => {
    if (document.hidden) stop()
    else if (!reducedMotion()) start()
  }
  window.addEventListener('resize', onResize, { passive: true })
  document.addEventListener('visibilitychange', onVis)
})

onBeforeUnmount(() => {
  stop()
  if (onResize) window.removeEventListener('resize', onResize)
  if (onVis)    document.removeEventListener('visibilitychange', onVis)
})
</script>

<template>
  <canvas
    ref="canvas"
    aria-hidden="true"
    style="position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 0; pointer-events: none;"
  ></canvas>
</template>
