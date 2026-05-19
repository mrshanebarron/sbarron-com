<script setup>
/**
 * Scroll-driven hero stack — Flex-IT theme port.
 *
 * 4 panels. Sticky-positioned hero (100vh) inside a 4-viewport spacer.
 * Scroll advances panels. Each panel: text left, treated photo right.
 *
 * When user-provided nano-banana images are present at
 * /hero/01-build.jpg etc, they take precedence; otherwise falls
 * back to the Unsplash placeholders (workshop, circuit, keyboard).
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const panels = [
  {
    img: '/hero/01-build.jpg',
    fallback: '/hero/workshop.jpg',
    label: 'Build',
    headline: 'Enterprise software,',
    accent: 'in hours.',
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

// On image load failure, swap to fallback path.
const heroImgRefs = ref([])
function onImgErr(e, panel) {
  if (e.target.src.endsWith(panel.img)) {
    e.target.src = panel.fallback
  }
}

const stage = ref(null)
const spacer = ref(null)
const progress = ref(0)
let rafId

function onScroll() {
  if (!spacer.value) return
  const rect = spacer.value.getBoundingClientRect()
  const spacerHeight = spacer.value.offsetHeight
  const viewport = window.innerHeight
  const raw = -rect.top / (spacerHeight - viewport)
  progress.value = Math.max(0, Math.min(1, raw))
}

function loop() {
  onScroll()
  rafId = requestAnimationFrame(loop)
}

const activeIndex = computed(() => {
  const p = progress.value * (panels.length - 0.0001)
  return Math.floor(p)
})

function panelOpacity(i) {
  const p = progress.value * (panels.length - 1)
  const distance = Math.abs(p - i)
  if (distance >= 1) return 0
  return 1 - distance
}

function panelImgX(i) {
  const p = progress.value * (panels.length - 1)
  const delta = (p - i) * 40
  return `translateX(${delta}px) scale(1.04)`
}

onMounted(() => {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  if (!reduce) rafId = requestAnimationFrame(loop)
})

onBeforeUnmount(() => {
  if (rafId) cancelAnimationFrame(rafId)
})

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
  <div ref="spacer" class="hero-spacer">
    <div ref="stage" class="hero-sticky">

      <div
        v-for="(panel, i) in panels"
        :key="i"
        class="hero-panel"
        :class="{ 'is-active': i === activeIndex }"
        :style="{
          opacity: panelOpacity(i),
          zIndex: i === activeIndex ? 3 : 2,
          pointerEvents: i === activeIndex ? 'auto' : 'none',
        }"
      >
        <div class="container-wide hero-grid">
          <!-- LEFT: text -->
          <div class="hero-text">
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

          <!-- RIGHT: image, framed -->
          <div class="hero-image-wrap">
            <div class="hero-image" :style="{ transform: panelImgX(i) }">
              <img :src="panel.img" :alt="panel.label" loading="eager" @error="onImgErr($event, panel)" />
              <div class="hero-image-overlay"></div>
            </div>
            <!-- Decorative cyan brackets, Flex-IT style -->
            <span class="hero-bracket hero-bracket-tl" aria-hidden="true"></span>
            <span class="hero-bracket hero-bracket-br" aria-hidden="true"></span>
          </div>
        </div>
      </div>

      <!-- Dot nav, vertical on right -->
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
  height: calc(100vh * 4);
}

.hero-sticky {
  position: sticky;
  top: 0;
  height: 100vh;
  width: 100%;
  overflow: hidden;
  background: var(--ink);
  isolation: isolate;
}

/* Faint cyan-blue radial wash in the corners, sets the Flex-IT mood */
.hero-sticky::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 80% 20%, rgba(11, 182, 238, 0.10), transparent 50%),
    radial-gradient(ellipse at 15% 85%, rgba(154, 56, 255, 0.08), transparent 55%);
  z-index: 0;
}

.hero-panel {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  padding-top: 100px; /* clear the masthead */
  transition: opacity 600ms cubic-bezier(0.4, 0, 0.2, 1);
}

.hero-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 3rem;
  align-items: center;
}
@media (min-width: 900px) {
  .hero-grid { grid-template-columns: 1fr 1.05fr; gap: 4rem; }
}

.hero-text { position: relative; z-index: 2; }
.hero-display {
  font-size: clamp(2.5rem, 6.5vw, 5.5rem);
  line-height: 1.05;
}
.hero-lede {
  max-width: 48ch;
  margin-top: 1.5rem;
}
.hero-ctas {
  display: flex;
  gap: 0.85rem;
  flex-wrap: wrap;
  margin-top: 2rem;
}

/* RIGHT image — bracketed, treated */
.hero-image-wrap {
  position: relative;
  aspect-ratio: 4 / 5;
  max-height: 70vh;
  isolation: isolate;
}
.hero-image {
  position: absolute;
  inset: 0;
  overflow: hidden;
  border-radius: 4px;
  transition: transform 600ms cubic-bezier(0.4, 0, 0.2, 1);
}
.hero-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  filter: saturate(1.1) contrast(1.05);
}
.hero-image-overlay {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 30% 70%, rgba(11, 182, 238, 0.18), transparent 65%),
    linear-gradient(135deg, transparent 30%, rgba(14, 15, 30, 0.7) 100%);
  pointer-events: none;
}
.hero-bracket {
  position: absolute;
  width: 32px;
  height: 32px;
  border: 2px solid var(--oxblood);
  z-index: 3;
}
.hero-bracket-tl { top: -8px; left: -8px; border-right: none; border-bottom: none; }
.hero-bracket-br { bottom: -8px; right: -8px; border-left: none; border-top: none; }

/* Dots — vertical right side */
.hero-dots {
  position: absolute;
  right: clamp(1rem, 3vw, 2.5rem);
  top: 50%;
  transform: translateY(-50%);
  z-index: 5;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
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
  transition: color 200ms ease;
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
  transition: background 200ms ease, width 200ms ease;
}
.hero-dot.is-active .hero-dot-bar {
  background: var(--oxblood);
  width: 28px;
}

.hero-scroll-hint {
  position: absolute;
  left: clamp(1.5rem, 4vw, 4rem);
  bottom: 1.5rem;
  z-index: 5;
  color: var(--bone-deep);
  font-family: var(--font-sans);
  font-size: 11px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  opacity: 0.6;
}
.hero-scroll-bar {
  display: block;
  width: 40px;
  height: 1px;
  background: linear-gradient(90deg, var(--oxblood), transparent);
  animation: scroll-pulse 2s ease-in-out infinite;
}
@keyframes scroll-pulse {
  0%, 100% { transform: scaleX(0.4); transform-origin: left; opacity: 0.4; }
  50%      { transform: scaleX(1);   transform-origin: left; opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
  .hero-panel, .hero-image { transition: none; }
  .hero-scroll-bar { animation: none; }
}
</style>
