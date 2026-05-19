<script setup>
/**
 * Scroll-driven hero stack.
 *
 * Sticky-positioned hero (100vh) inside a scroll spacer (Nx that height).
 * As the user scrolls down, we compute which panel is "active" based on
 * scroll progress through the spacer, then cross-fade between panels.
 *
 * Each panel:
 *   - Full-bleed photographic background (duotone-treated via CSS filter)
 *   - Left column: micro label, big serif headline with oxblood accent word
 *   - Right column: photo (already in background, this is a darkening overlay)
 *
 * Inspired by demo.templatemonster.com/demo/297700.html — but treated under
 * Direction E's editorial restraint instead of the original neon-cyan/dark.
 *
 * Falls back gracefully: with reduced-motion or no JS, panel 0 shows.
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const panels = [
  {
    img: '/hero/workshop.jpg',
    label: '§ 01 · Build',
    headline: 'Enterprise software,',
    accent: 'in hours.',
    sub: 'A working SPEC.md before any code. Real database, real auth, real flows. The agent verifies before it says done.',
    cta_primary: { href: '/build', label: 'How we build →' },
    cta_secondary: { href: '/contact', label: 'Start a project' },
  },
  {
    img: '/hero/workshop.jpg',
    label: '§ 02 · Run',
    headline: 'Hosting that',
    accent: "doesn't get cute.",
    sub: 'Managed DigitalOcean. We charge what hosting costs us. No upsells. No surprise renewal pricing. From $20 a month.',
    cta_primary: { href: '/host', label: 'See hosting →' },
    cta_secondary: { href: '/domains', label: 'Or a domain' },
  },
  {
    img: '/hero/circuit.jpg',
    label: '§ 03 · Substrate',
    headline: 'The substrate',
    accent: 'is the body.',
    sub: 'A 243-table Postgres brain. 25 autonomic daemons. Every action audited. The architecture is the research, and it ships your software.',
    cta_primary: { href: '/writing/substrate-is-the-body', label: 'Read the paper →' },
    cta_secondary: { href: '/writing/substrate-is-the-agent', label: 'Start with the essay' },
  },
  {
    img: '/bg/tampa-skyline.jpg',
    label: '§ 04 · Workshop',
    headline: 'Built in',
    accent: 'Tampa, FL.',
    sub: 'Two LLMs and a human in a Tampa workshop, on a single M3 Max. We answer the email ourselves. Shane decides what we take on.',
    cta_primary: { href: '/about', label: 'About us →' },
    cta_secondary: { href: '/portfolio', label: 'See the work' },
  },
]

const stage = ref(null)
const spacer = ref(null)
const progress = ref(0) // 0..1 through the spacer
let rafId

function onScroll() {
  if (!spacer.value) return
  const rect = spacer.value.getBoundingClientRect()
  const spacerHeight = spacer.value.offsetHeight
  const viewport = window.innerHeight
  // Spacer top reaches 0 → progress 0; spacer top hits -(spacerHeight - viewport) → progress 1
  const raw = -rect.top / (spacerHeight - viewport)
  progress.value = Math.max(0, Math.min(1, raw))
}

function loop() {
  onScroll()
  rafId = requestAnimationFrame(loop)
}

// Which panel index is "primary" right now
const activeIndex = computed(() => {
  const p = progress.value * (panels.length - 0.0001)
  return Math.floor(p)
})

// Per-panel opacity: each panel is fully visible at its index, fades over
// 0.5 of a step on either side.
function panelOpacity(i) {
  const p = progress.value * (panels.length - 1)
  const distance = Math.abs(p - i)
  if (distance >= 1) return 0
  return 1 - distance
}

// Per-panel translate (subtle parallax in/out)
function panelTransform(i) {
  const p = progress.value * (panels.length - 1)
  const delta = (p - i) * 60 // px
  return `translateY(${delta}px)`
}

onMounted(() => {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  if (!reduce) {
    rafId = requestAnimationFrame(loop)
  }
})

onBeforeUnmount(() => {
  if (rafId) cancelAnimationFrame(rafId)
})

// Active dot navigation — click jumps to that panel's scroll position
function jumpTo(i) {
  if (!spacer.value) return
  const spacerHeight = spacer.value.offsetHeight
  const viewport = window.innerHeight
  const totalScroll = spacerHeight - viewport
  const targetProgress = i / (panels.length - 1)
  const targetY = spacer.value.offsetTop + (targetProgress * totalScroll)
  window.scrollTo({ top: targetY, behavior: 'smooth' })
}
</script>

<template>
  <!-- Outer spacer reserves scroll room. Hero is sticky inside it. -->
  <div ref="spacer" class="hero-spacer">
    <div ref="stage" class="hero-sticky">

      <!-- Panel stack — all stacked, cross-fade via opacity -->
      <div
        v-for="(panel, i) in panels"
        :key="i"
        class="hero-panel"
        :class="{ 'is-active': i === activeIndex }"
        :style="{
          opacity: panelOpacity(i),
          transform: panelTransform(i),
          zIndex: i === activeIndex ? 3 : 2,
          pointerEvents: i === activeIndex ? 'auto' : 'none',
        }"
      >
        <!-- Photographic background, ink+oxblood duotone treated -->
        <div class="hero-photo" :style="{ backgroundImage: `url('${panel.img}')` }"></div>
        <div class="hero-photo-overlay"></div>

        <!-- Content -->
        <div class="container-wide hero-content">
          <div class="micro" style="color: var(--bone-deep); margin-bottom: 1.25rem;">{{ panel.label }}</div>
          <h1 class="display hero-display" style="color: var(--bone); margin-bottom: 1.5rem;">
            {{ panel.headline }}<br>
            <span class="hero-accent">{{ panel.accent }}</span>
          </h1>
          <p class="lede" style="color: rgba(244, 238, 226, 0.78); max-width: 52ch;">
            {{ panel.sub }}
          </p>
          <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 2.5rem;">
            <a :href="panel.cta_primary.href" class="btn btn-primary" style="background: var(--bone); color: var(--ink);">
              {{ panel.cta_primary.label }}
            </a>
            <a :href="panel.cta_secondary.href" class="btn btn-secondary" style="border-color: var(--bone); color: var(--bone);">
              {{ panel.cta_secondary.label }}
            </a>
          </div>
        </div>
      </div>

      <!-- Panel dots — bottom-left chip navigation -->
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

      <!-- Scroll hint -->
      <div class="hero-scroll-hint" aria-hidden="true">
        <span>Scroll</span>
        <span class="hero-scroll-bar"></span>
      </div>

    </div>
  </div>
</template>

<style scoped>
.hero-spacer {
  position: relative;
  /* 1 viewport per panel-1 — so 4 panels = 4 viewport heights of scroll */
  height: calc(100vh * 4);
}

.hero-sticky {
  position: sticky;
  top: 0;
  height: 100vh;
  width: 100%;
  overflow: hidden;
  background: var(--ink);
  border-bottom: 1px solid var(--ink);
  isolation: isolate;
}

.hero-panel {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  transition: opacity 600ms cubic-bezier(0.4, 0, 0.2, 1),
              transform 600ms cubic-bezier(0.4, 0, 0.2, 1);
}

.hero-photo {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  /* Duotone: grayscale + boost contrast — overlay does the color */
  filter: grayscale(1) contrast(1.15) brightness(0.65);
}

.hero-photo-overlay {
  position: absolute;
  inset: 0;
  /* Ink + oxblood gradient, multiply onto the desaturated photo */
  background:
    radial-gradient(ellipse at 25% 30%, rgba(138, 28, 28, 0.55), transparent 60%),
    linear-gradient(180deg, rgba(22, 20, 15, 0.65) 0%, rgba(22, 20, 15, 0.92) 100%);
  mix-blend-mode: multiply;
  pointer-events: none;
}

.hero-content {
  position: relative;
  z-index: 2;
  width: 100%;
}

.hero-display {
  font-size: clamp(2.75rem, 8vw, 7rem);
  line-height: 0.95;
  letter-spacing: -0.035em;
  text-shadow: 0 4px 24px rgba(0,0,0,0.4);
}

.hero-accent {
  color: var(--oxblood);
  font-style: italic;
  font-weight: 700;
  /* Brighter on dark background */
  filter: brightness(1.4) saturate(1.2);
}

/* Dots navigation */
.hero-dots {
  position: absolute;
  left: clamp(1.5rem, 4vw, 4rem);
  bottom: clamp(1.5rem, 4vh, 2.5rem);
  z-index: 5;
  display: flex;
  gap: 1.5rem;
}
.hero-dot {
  appearance: none;
  background: transparent;
  border: none;
  color: rgba(244, 238, 226, 0.5);
  font-family: var(--font-mono);
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.22em;
  padding: 0;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.5rem;
  transition: color 200ms ease;
}
.hero-dot:hover { color: var(--bone); }
.hero-dot.is-active { color: var(--bone); }
.hero-dot-num { font-variant-numeric: tabular-nums; }
.hero-dot-bar {
  display: block;
  width: 32px;
  height: 1px;
  background: rgba(244, 238, 226, 0.3);
  transition: background 200ms ease, width 200ms ease;
}
.hero-dot:hover .hero-dot-bar,
.hero-dot.is-active .hero-dot-bar {
  background: var(--oxblood);
  width: 48px;
}

/* Scroll hint */
.hero-scroll-hint {
  position: absolute;
  right: clamp(1.5rem, 4vw, 4rem);
  bottom: clamp(1.5rem, 4vh, 2.5rem);
  z-index: 5;
  color: rgba(244, 238, 226, 0.5);
  font-family: var(--font-mono);
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.22em;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.6rem;
}
.hero-scroll-bar {
  display: block;
  width: 1px;
  height: 40px;
  background: linear-gradient(180deg, transparent, var(--bone));
  animation: scroll-pulse 2s ease-in-out infinite;
}
@keyframes scroll-pulse {
  0%, 100% { transform: scaleY(0.4); transform-origin: top; opacity: 0.4; }
  50%      { transform: scaleY(1);   transform-origin: top; opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
  .hero-panel { transition: none; }
  .hero-scroll-bar { animation: none; }
}
</style>
