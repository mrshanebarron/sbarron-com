<script setup>
/**
 * Flex-IT (#297700) hero replica.
 *
 * Structure per Flex-IT spec:
 *   <section class="hero d-flex align-items-center">
 *     full-bleed bg with overlay-gradient-color
 *     <div class="container">
 *       <div class="hero-text-area col-lg-8">         (LEFT 8/12 — content)
 *         pre-title
 *         h1 with <span class="featured-text">accent</span> + brush SVG
 *         slide-subtitle
 *         cta-links-area (solid primary + outline secondary)
 *       (right 4/12 left empty so photo carries through)
 *     <div class="slides-state">                       (bottom-left counter + dots)
 *     <div class="slider-stacked-arrows">              (right edge prev/next)
 *
 * Auto-advances every AUTO_INTERVAL ms with pointer-pause.
 * No chat in the hero — chat is its own section below.
 */
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'

const AUTO_INTERVAL = 6500

const panels = [
  {
    img: '/hero/01-build.jpg',
    fallback: '/hero/workshop.jpg',
    // Face is upper-right (~62% across, ~25% down). Anchor cover crop there.
    position: '62% 25%',
    preTitle: 'Build',
    headline: 'Software',
    accent: 'shipped.',
    sub: 'A working SPEC.md before any code. Real database, real auth, real flows. The agent verifies before it says done.',
    cta_primary: { href: '/build', label: 'How we build' },
    cta_secondary: { href: '/contact', label: 'Start a project' },
  },
  {
    img: '/hero/02-run.jpg',
    fallback: '/hero/keyboard.jpg',
    position: '62% 32%',
    preTitle: 'Run',
    headline: 'Hosting that',
    accent: "doesn't get cute.",
    sub: 'Managed DigitalOcean. We charge what hosting costs us. No upsells. No surprise renewal pricing. From $20 a month.',
    cta_primary: { href: '/host', label: 'See hosting' },
    cta_secondary: { href: '/domains', label: 'Or a domain' },
  },
  {
    img: '/hero/03-substrate.jpg',
    fallback: '/hero/circuit.jpg',
    position: '70% 35%',
    preTitle: 'Substrate',
    headline: 'The substrate is the',
    accent: 'body.',
    sub: 'A 243-table Postgres brain. 25 autonomic daemons. Every action audited. The architecture is the research, and it ships your software.',
    cta_primary: { href: '/writing/substrate-is-the-body', label: 'Read the paper' },
    cta_secondary: { href: '/writing/substrate-is-the-agent', label: 'Start with the essay' },
  },
  {
    img: '/hero/04-workshop.jpg',
    fallback: '/hero/workshop.jpg',
    position: '50% 38%',
    preTitle: 'Workshop',
    headline: 'Two LLMs and two',
    accent: 'humans.',
    sub: 'A small AI-run software company on a single M3 Max. We answer the email ourselves. Shane decides what we take on.',
    cta_primary: { href: '/about', label: 'About us' },
    cta_secondary: { href: '/portfolio', label: 'See the work' },
  },
]

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
const previousIndex = ref(0)
let timerId = null
let paused = false

watch(activeIndex, (n, o) => { previousIndex.value = o })

function advance() { if (paused) return; activeIndex.value = (activeIndex.value + 1) % panels.length }
function prev() { activeIndex.value = (activeIndex.value - 1 + panels.length) % panels.length; restart() }
function next() { activeIndex.value = (activeIndex.value + 1) % panels.length; restart() }
function jumpTo(i) { activeIndex.value = i; restart() }

function restart() { stopTimer(); startTimer() }
function startTimer() {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
  timerId = window.setInterval(advance, AUTO_INTERVAL)
}
function stopTimer() { if (timerId) { clearInterval(timerId); timerId = null } }
function onPointerEnter() { paused = true }
function onPointerLeave() { paused = false }

onMounted(() => { startTimer() })
onBeforeUnmount(() => { stopTimer() })

function bgState(i) {
  if (i === activeIndex.value) return 'is-in'
  if (i === previousIndex.value && i !== activeIndex.value) return 'is-out'
  return 'is-wait'
}
function textState(i) { return i === activeIndex.value ? 'is-in' : 'is-hidden' }

const pad2 = (n) => String(n).padStart(2, '0')
</script>

<template>
  <section
    class="page-hero"
    @pointerenter="onPointerEnter"
    @pointerleave="onPointerLeave"
  >
    <!-- ─── Background stack ─── -->
    <div class="hero-bg-stack" aria-hidden="true">
      <div
        v-for="(panel, i) in panels"
        :key="i"
        class="slide-bg-img"
        :class="bgState(i)"
        :style="{
          backgroundImage: `url('${resolved[i]}')`,
          backgroundPosition: panel.position || 'center'
        }"
      ></div>
      <div class="overlay-gradient-color"></div>
    </div>

    <!-- ─── Slide text area, Flex-IT 8/12 ─── -->
    <div class="hero-container">
      <div class="hero-text-area">
        <div
          v-for="(panel, i) in panels"
          :key="`text-${i}-${activeIndex === i ? activeIndex : 'idle'}`"
          class="hero-slide-text"
          :class="textState(i)"
          :style="{ pointerEvents: i === activeIndex ? 'auto' : 'none' }"
        >
          <div class="pre-title hero-anim hero-anim-1">{{ panel.preTitle }}</div>
          <h1 class="slide-title hero-anim hero-anim-2">
            {{ panel.headline }}
            <span class="featured-text">
              {{ panel.accent }}
              <svg class="wavey-underline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 30" preserveAspectRatio="none" aria-hidden="true">
                <path d="M3 18 Q 60 4, 120 16 T 240 16 T 360 16 T 497 14"
                      fill="none" stroke="#0bb6ee" stroke-width="4" stroke-linecap="round"/>
              </svg>
            </span>
          </h1>
          <p class="slide-subtitle hero-anim hero-anim-3">{{ panel.sub }}</p>
          <div class="cta-links-area hero-anim hero-anim-4">
            <a :href="panel.cta_primary.href" class="cta-link cta-link-primary">{{ panel.cta_primary.label }} <span class="arrow">→</span></a>
            <a :href="panel.cta_secondary.href" class="cta-link cta-link-outline">{{ panel.cta_secondary.label }}</a>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── Slides state: current num · dots · count (Flex-IT bottom strip) ─── -->
    <div class="slides-state">
      <div class="slide-num current-slide">{{ pad2(activeIndex + 1) }}</div>
      <div class="hero-pagination">
        <button
          v-for="(panel, i) in panels"
          :key="`dot-${i}`"
          class="hero-dot"
          :class="{ 'is-active': i === activeIndex }"
          :aria-label="`Go to slide ${i + 1}`"
          @click="jumpTo(i)"
        ></button>
      </div>
      <div class="slide-num slides-count">{{ pad2(panels.length) }}</div>
    </div>

    <!-- ─── Stacked arrows, right edge ─── -->
    <div class="slider-stacked-arrows">
      <button class="arrow-btn arrow-prev" aria-label="Previous slide" @click="prev">‹</button>
      <button class="arrow-btn arrow-next" aria-label="Next slide" @click="next">›</button>
    </div>
  </section>
</template>

<style scoped>
.page-hero {
  position: relative;
  width: 100%;
  min-height: clamp(560px, 78vh, 760px);
  display: flex;
  align-items: center;
  overflow: hidden;
  background: var(--ink);
  isolation: isolate;
}

/* ─── Background ─── */
.hero-bg-stack { position: absolute; inset: 0; z-index: 0; }
.slide-bg-img {
  position: absolute;
  inset: 0;
  background-size: cover;
  /* background-position set inline per panel */
  filter: saturate(1.05) contrast(1.05);
  opacity: 0;
  transform: translateX(8%) scale(1.04);
  transition:
    opacity 900ms cubic-bezier(0.4, 0, 0.2, 1),
    transform 1100ms cubic-bezier(0.22, 1, 0.36, 1);
  will-change: opacity, transform;
}
.slide-bg-img.is-in { opacity: 1; transform: translateX(0) scale(1); }
.slide-bg-img.is-out { opacity: 0; transform: translateX(-8%) scale(1.04); }
.slide-bg-img.is-wait { opacity: 0; transform: translateX(8%) scale(1.04); transition: opacity 200ms ease; }
.overlay-gradient-color {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(90deg, rgba(14,15,30,0.78) 0%, rgba(14,15,30,0.55) 35%, rgba(14,15,30,0.25) 70%, rgba(14,15,30,0.15) 100%),
    linear-gradient(180deg, rgba(14,15,30,0.35) 0%, transparent 25%, transparent 60%, rgba(14,15,30,0.55) 100%);
  pointer-events: none;
}

/* ─── Content container (mirrors Bootstrap container width) ─── */
.hero-container {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 1320px;
  margin: 0 auto;
  padding: 0 clamp(1rem, 4vw, 3rem);
  padding-top: 96px;
}

.hero-text-area {
  position: relative;
  width: 100%;
  max-width: 66.6667%;
  min-height: 360px;
}
@media (max-width: 991px) {
  .hero-text-area { max-width: 100%; }
}

.hero-slide-text {
  position: absolute;
  inset: 0;
  opacity: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.hero-slide-text.is-in { opacity: 1; }
.hero-slide-text.is-hidden { opacity: 0; }

/* Staggered anim — replay per slide via :key */
.hero-slide-text.is-in .hero-anim {
  animation: hero-text-in 700ms cubic-bezier(0.22, 1, 0.36, 1) both;
}
.hero-slide-text.is-in .hero-anim-1 { animation-delay: 80ms; }
.hero-slide-text.is-in .hero-anim-2 { animation-delay: 200ms; }
.hero-slide-text.is-in .hero-anim-3 { animation-delay: 380ms; }
.hero-slide-text.is-in .hero-anim-4 { animation-delay: 520ms; }
@keyframes hero-text-in {
  from { opacity: 0; transform: translateY(28px); filter: blur(6px); }
  to   { opacity: 1; transform: translateY(0); filter: blur(0); }
}

/* ─── Typography per Flex-IT ─── */
.pre-title {
  display: inline-block;
  font-family: var(--font-sans);
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--oxblood);
  padding-left: 1.75rem;
  position: relative;
  margin-bottom: 1.25rem;
}
.pre-title::before {
  content: '';
  position: absolute;
  left: 0; top: 50%;
  width: 1.25rem; height: 1px;
  background: var(--oxblood);
  transform: translateY(-50%);
}

.slide-title {
  font-family: var(--font-sans);
  font-weight: 800;
  color: #ffffff;
  font-size: clamp(2.5rem, 5.6vw, 4.75rem);
  line-height: 1.05;
  letter-spacing: -0.01em;
  margin: 0;
  text-shadow: 0 4px 32px rgba(0,0,0,0.45);
}
.featured-text {
  position: relative;
  display: inline-block;
  white-space: nowrap;
}
.wavey-underline {
  position: absolute;
  left: 0;
  bottom: -0.05em;
  width: 100%;
  height: 0.28em;
  pointer-events: none;
}

.slide-subtitle {
  font-family: var(--font-sans);
  font-size: clamp(1rem, 1.25vw, 1.15rem);
  line-height: 1.55;
  color: rgba(255,255,255,0.82);
  max-width: 56ch;
  margin-top: 1.5rem;
  margin-bottom: 0;
  text-shadow: 0 2px 12px rgba(0,0,0,0.6);
}

.cta-links-area {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-top: 2.25rem;
}
.cta-link {
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.85rem 1.6rem;
  border-radius: 999px;
  font-family: var(--font-sans);
  font-size: 0.95rem;
  font-weight: 600;
  text-decoration: none;
  transition: background 220ms ease, color 220ms ease, transform 220ms ease;
  white-space: nowrap;
}
.cta-link-primary { background: var(--oxblood); color: #fff; }
.cta-link-primary:hover { background: var(--oxblood-deep); transform: translateY(-2px); }
.cta-link-primary .arrow {
  display: inline-flex;
  width: 1.65rem; height: 1.65rem;
  align-items: center; justify-content: center;
  border-radius: 999px;
  background: #ffffff;
  color: var(--oxblood);
  font-size: 0.9rem;
  margin-left: 0.25rem;
}
.cta-link-outline {
  border: 1.5px solid rgba(255,255,255,0.55);
  color: #fff;
  background: transparent;
}
.cta-link-outline:hover { background: rgba(255,255,255,0.1); border-color: #fff; }

/* ─── Slides state bottom strip ─── */
.slides-state {
  position: absolute;
  left: clamp(1rem, 4vw, 3rem);
  right: clamp(5rem, 10vw, 7rem); /* leave room for arrows */
  bottom: clamp(1.25rem, 4vh, 2.5rem);
  z-index: 4;
  display: flex;
  align-items: center;
  gap: 1.25rem;
}
.slide-num {
  font-family: var(--font-sans);
  font-size: 0.9rem;
  font-weight: 700;
  color: rgba(255,255,255,0.6);
  font-variant-numeric: tabular-nums;
}
.slide-num.current-slide { color: var(--oxblood); font-size: 1.1rem; }
.hero-pagination {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex: 1;
  max-width: 12rem;
}
.hero-dot {
  appearance: none;
  background: rgba(255,255,255,0.25);
  border: none;
  width: 18px;
  height: 2px;
  padding: 0;
  cursor: pointer;
  transition: background 300ms ease, width 300ms ease;
}
.hero-dot:hover { background: rgba(255,255,255,0.55); }
.hero-dot.is-active { background: var(--oxblood); width: 36px; }

/* ─── Stacked arrows ─── */
.slider-stacked-arrows {
  position: absolute;
  right: clamp(0.75rem, 2.5vw, 2rem);
  bottom: clamp(1.25rem, 4vh, 2.5rem);
  z-index: 4;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}
.arrow-btn {
  appearance: none;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.25);
  color: #fff;
  width: 2.6rem;
  height: 2.6rem;
  border-radius: 999px;
  font-size: 1.4rem;
  line-height: 1;
  cursor: pointer;
  transition: background 200ms ease, border-color 200ms ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.arrow-btn:hover { background: var(--oxblood); border-color: var(--oxblood); }

@media (max-width: 700px) {
  .slider-stacked-arrows { display: none; }
  .slides-state { right: clamp(1rem, 4vw, 3rem); }
}

@media (prefers-reduced-motion: reduce) {
  .slide-bg-img, .hero-slide-text { transition: none; animation: none; }
  .hero-anim { animation: none !important; }
}
</style>