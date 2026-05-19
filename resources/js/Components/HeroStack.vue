<script setup>
/**
 * Hero with auto-advancing background portrait + persistent PneumaChat.
 *
 * Behavior change from prior version: no scroll-pinning. The hero is a
 * single full-height (clamped) section. Background image and headline
 * text cross-fade automatically every AUTO_INTERVAL ms. Dots on the
 * right let the visitor jump panels; hover pauses autoplay.
 *
 * PneumaChat sits in the right column for every panel and does not
 * change as panels advance.
 */
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import PneumaChat from '@/Components/PneumaChat.vue'

const AUTO_INTERVAL = 6500 // ms per panel

const panels = [
  {
    img: '/hero/01-build.jpg',
    fallback: '/hero/workshop.jpg',
    label: 'Build',
    headline: 'Software,',
    accent: 'shipped.',
    sub: 'A working SPEC.md before any code. Real database, real auth, real flows. The agent verifies before it says done.',
    cta_primary: { href: '/build', label: 'How we build' },
    cta_secondary: { href: '/contact', label: 'Start a project' },
  },
  {
    img: '/hero/02-run.jpg',
    fallback: '/hero/keyboard.jpg',
    label: 'Run',
    headline: "Hosting that",
    accent: "doesn't get cute.",
    sub: 'Managed DigitalOcean. We charge what hosting costs us. No upsells. No surprise renewal pricing. From $20 a month.',
    cta_primary: { href: '/host', label: 'See hosting' },
    cta_secondary: { href: '/domains', label: 'Or a domain' },
  },
  {
    img: '/hero/03-substrate.jpg',
    fallback: '/hero/circuit.jpg',
    label: 'Substrate',
    headline: 'The substrate',
    accent: 'is the body.',
    sub: 'A 243-table Postgres brain. 25 autonomic daemons. Every action audited. The architecture is the research, and it ships your software.',
    cta_primary: { href: '/writing/substrate-is-the-body', label: 'Read the paper' },
    cta_secondary: { href: '/writing/substrate-is-the-agent', label: 'Start with the essay' },
  },
  {
    img: '/hero/04-workshop.jpg',
    fallback: '/hero/workshop.jpg',
    label: 'Workshop',
    headline: 'Two LLMs and',
    accent: 'a human.',
    sub: 'A small AI-run software company on a single M3 Max. We answer the email ourselves. Shane decides what we take on.',
    cta_primary: { href: '/about', label: 'About us' },
    cta_secondary: { href: '/portfolio', label: 'See the work' },
  },
]

// Preload + fallback chain
const resolved = ref(panels.map(p => p.img))
onMounted(() => {
  panels.forEach((p, i) => {
    const img = new Image()
    img.onload = () => { resolved.value[i] = p.img }
    img.onerror = () => { resolved.value[i] = p.fallback }
    img.src = p.img
  })
})

const activeIndex = ref(0)
let timerId = null
let paused = false

function advance() {
  if (paused) return
  activeIndex.value = (activeIndex.value + 1) % panels.length
}

function startTimer() {
  stopTimer()
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
  timerId = window.setInterval(advance, AUTO_INTERVAL)
}
function stopTimer() {
  if (timerId) { clearInterval(timerId); timerId = null }
}

function jumpTo(i) {
  activeIndex.value = i
  // Reset timer so visitor gets a full interval after manual click
  startTimer()
}

function onPointerEnter() { paused = true }
function onPointerLeave() { paused = false }

onMounted(() => { startTimer() })
onBeforeUnmount(() => { stopTimer() })

function panelOpacity(i) {
  return i === activeIndex.value ? 1 : 0
}
</script>

<template>
  <section
    class="hero"
    @pointerenter="onPointerEnter"
    @pointerleave="onPointerLeave"
  >

    <!-- BACKGROUND — full-bleed portrait, cross-fades per panel -->
    <div class="hero-bg-stack" aria-hidden="true">
      <div
        v-for="(panel, i) in panels"
        :key="i"
        class="hero-bg"
        :style="{
          backgroundImage: `url('${resolved[i]}')`,
          opacity: panelOpacity(i),
        }"
      ></div>
      <div class="hero-bg-overlay"></div>
    </div>

    <!-- CONTENT — text left (cross-faded), Pneuma chat right (persistent) -->
    <div class="container-wide hero-grid">

      <div class="hero-text-stack">
        <div
          v-for="(panel, i) in panels"
          :key="i"
          class="hero-text"
          :style="{
            opacity: panelOpacity(i),
            zIndex: i === activeIndex ? 3 : 2,
            pointerEvents: i === activeIndex ? 'auto' : 'none',
          }"
        >
          <div class="micro-flex">{{ panel.label }}</div>
          <h1 class="display hero-display">
            {{ panel.headline }}<br>
            <span class="mark">{{ panel.accent }}</span>
          </h1>
          <p class="lede hero-lede">{{ panel.sub }}</p>
          <div class="hero-ctas">
            <a :href="panel.cta_primary.href" class="btn btn-primary">{{ panel.cta_primary.label }}</a>
            <a :href="panel.cta_secondary.href" class="btn btn-secondary">{{ panel.cta_secondary.label }}</a>
          </div>
        </div>
      </div>

      <div class="hero-chat-wrap">
        <PneumaChat
          :embedded="true"
          :accent="'#0bb6ee'"
          :bg="'rgba(14, 15, 30, 0.78)'"
          :fg="'#ffffff'"
        />
      </div>
    </div>

    <!-- Dot nav, vertical right -->
    <nav class="hero-dots" aria-label="Hero sections">
      <button
        v-for="(panel, i) in panels"
        :key="i"
        @click="jumpTo(i)"
        class="hero-dot"
        :class="{ 'is-active': i === activeIndex }"
        :aria-label="`Go to ${panel.label}`"
      >
        <span class="hero-dot-num">{{ String(i + 1).padStart(2, '0') }}</span>
        <span class="hero-dot-bar"></span>
      </button>
    </nav>

  </section>
</template>

<style scoped>
.hero {
  position: relative;
  min-height: clamp(640px, 92vh, 920px);
  width: 100%;
  overflow: hidden;
  background: var(--ink);
  isolation: isolate;
}

/* ─── Background layer ─── */
.hero-bg-stack {
  position: absolute;
  inset: 0;
  z-index: 0;
}
.hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  transition: opacity 900ms cubic-bezier(0.4, 0, 0.2, 1);
  filter: saturate(1.05) contrast(1.05);
  will-change: opacity;
}
.hero-bg-overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(90deg, rgba(14, 15, 30, 0.92) 0%, rgba(14, 15, 30, 0.55) 45%, rgba(14, 15, 30, 0.15) 100%),
    radial-gradient(ellipse at 85% 15%, rgba(11, 182, 238, 0.25), transparent 55%),
    linear-gradient(180deg, rgba(14, 15, 30, 0.7) 0%, transparent 30%, rgba(14, 15, 30, 0.85) 100%);
  pointer-events: none;
}

/* ─── Content grid ─── */
.hero-grid {
  position: relative;
  z-index: 2;
  display: grid;
  grid-template-columns: 1fr;
  gap: 2.5rem;
  align-items: center;
  min-height: clamp(640px, 92vh, 920px);
  padding-top: 100px;
  padding-bottom: 80px;
}
@media (min-width: 1000px) {
  .hero-grid {
    grid-template-columns: 1.1fr 1fr;
    gap: 4rem;
  }
}

.hero-text-stack {
  position: relative;
  min-height: 50vh;
}
.hero-text {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  transition: opacity 700ms cubic-bezier(0.4, 0, 0.2, 1);
}

.hero-display {
  font-size: clamp(2.5rem, 6.5vw, 5.25rem);
  line-height: 1.05;
  color: var(--bone);
  text-shadow: 0 4px 32px rgba(0, 0, 0, 0.45);
}
.hero-lede {
  max-width: 48ch;
  margin-top: 1.5rem;
  color: var(--bone-deep);
  text-shadow: 0 2px 12px rgba(0, 0, 0, 0.6);
}
.hero-ctas {
  display: flex;
  gap: 0.85rem;
  flex-wrap: wrap;
  margin-top: 2rem;
}

.hero-chat-wrap {
  position: relative;
  z-index: 2;
  width: 100%;
  min-height: 480px;
  display: flex;
  align-items: center;
}
.hero-chat-wrap :deep(.chat-embedded) { width: 100%; }

/* Dots */
.hero-dots {
  position: absolute;
  right: clamp(0.75rem, 2vw, 1.5rem);
  top: 50%;
  transform: translateY(-50%);
  z-index: 5;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
@media (max-width: 999px) { .hero-dots { display: none; } }
.hero-dot {
  appearance: none;
  background: transparent;
  border: none;
  color: var(--bone-deep);
  font-family: var(--font-sans);
  font-size: 11px;
  font-weight: 600;
  padding: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  transition: color 200ms ease, opacity 200ms ease;
  opacity: 0.55;
}
.hero-dot:hover { opacity: 1; color: var(--bone); }
.hero-dot.is-active { opacity: 1; color: var(--bone); }
.hero-dot-num { font-variant-numeric: tabular-nums; }
.hero-dot-bar {
  display: block;
  width: 16px;
  height: 1px;
  background: var(--bone-deep);
  transition: background 600ms ease, width 600ms ease;
}
.hero-dot.is-active .hero-dot-bar {
  background: var(--oxblood);
  width: 28px;
}

@media (prefers-reduced-motion: reduce) {
  .hero-bg, .hero-text { transition: none; }
}
</style>
