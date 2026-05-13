<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import PneumaChat from '@/Components/PneumaChat.vue'
import TerminalCursor from '@/Components/TerminalCursor.vue'
import CountUp from '@/Components/CountUp.vue'

const props = defineProps({
  ticker: { type: Array, default: () => [] },
  matt: { type: Object, default: () => ({}) },     // generic "last shipped" payload (legacy key)
  portfolio: { type: Array, default: () => [] },
})

const STAGES = ['hero', 'pulse', 'proof', 'studio', 'tiers', 'engage']

const activeStage = ref(0)
let io = null

/* ─── Self-typing hero ─── */
const headlineLines = [
  { text: 'Enterprise software,', accent: false },
  { text: 'hours.', accent: true },
]
const typed = ref([{ text: '', accent: false }])
let typeTimers = []

function typeHero() {
  let li = 0
  let ci = 0
  const tick = () => {
    if (li >= headlineLines.length) return
    const target = headlineLines[li]
    if (ci === 0) typed.value[li] = { text: '', accent: target.accent }
    if (ci < target.text.length) {
      ci += 1
      typed.value[li] = { text: target.text.slice(0, ci), accent: target.accent }
      typeTimers.push(setTimeout(tick, 32 + Math.random() * 28))
    } else {
      li += 1
      ci = 0
      if (li < headlineLines.length) {
        typed.value.push({ text: '', accent: false })
        typeTimers.push(setTimeout(tick, 280))
      }
    }
  }
  typeTimers.push(setTimeout(tick, 600))
}

/* ─── Live commit ticker ─── */
const tickerLines = computed(() => {
  const seed = props.ticker?.length ? props.ticker : [
    { kind: 'commit', text: 'feat: ship quote pipeline · 14 files' },
    { kind: 'deploy', text: 'deploy → portal · green' },
    { kind: 'test',   text: '127 tests, 491 assertions, 0 failed' },
    { kind: 'commit', text: 'fix: stripe webhook idempotency on retry' },
    { kind: 'deploy', text: 'deploy → mindwell.app · green' },
    { kind: 'test',   text: 'PHPUnit · all green · 12.4s' },
    { kind: 'commit', text: 'feat: invoice CSV export · same-day shipped' },
  ]
  return [...seed, ...seed, ...seed]
})

const dotColor = (kind) => ({
  commit: '#ff5a3c',
  deploy: '#6e3bff',
  test:   '#00b894',
  vital:  '#ffd400',
}[kind] || '#888')

/* ─── Slot machine pricing ─── */
const SCOPES = [
  { label: 'Quick fix',           normal: '$3,500',  ours: '$1k', wall: '4 hr',   you: 'a bug, a form, a Stripe webhook' },
  { label: 'New feature',         normal: '$8,000',  ours: '$3k', wall: '1 day',  you: 'a booking flow, a CSV importer' },
  { label: 'Module + integration',normal: '$22,000', ours: '$8k', wall: '3 days', you: 'a quote pipeline, a portal module' },
  { label: 'Full production app', normal: '$70,000', ours: '$22k',wall: '2 weeks',you: 'auth, billing, admin, the works' },
]
const slotIdx = ref(0)
const spinning = ref(false)

function spin() {
  if (spinning.value) return
  spinning.value = true
  const next = (slotIdx.value + 1 + Math.floor(Math.random() * (SCOPES.length - 1))) % SCOPES.length
  slotIdx.value = next
  setTimeout(() => { spinning.value = false }, 1150)
}
const reelOffset = computed(() => `translateY(-${slotIdx.value * 6}rem)`)

/* ─── Pneuma narrator beats ─── */
const narrator = ref({ visible: false, text: '', stage: -1 })
const NARRATION = [
  null,
  { text: 'Watch this. Every line that ships, in real time.', after: 800 },
  { text: 'I built that. I sign every line.', after: 600 },
  { text: 'There are three of us. Hi.', after: 700 },
  { text: 'Pull the lever. Pick what you need.', after: 600 },
  { text: 'Or just talk to me — I\'m right up top.', after: 700 },
]

let lastAnnounced = -1
function announce(stage) {
  if (stage === lastAnnounced) return
  lastAnnounced = stage
  const beat = NARRATION[stage]
  if (!beat) { narrator.value.visible = false; return }
  setTimeout(() => {
    narrator.value = { visible: true, text: beat.text, stage }
    setTimeout(() => {
      if (narrator.value.stage === stage) narrator.value.visible = false
    }, 5200)
  }, beat.after)
}

/* ─── Mount ─── */
onMounted(() => {
  typeHero()
  io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting && e.intersectionRatio > 0.4) {
        const idx = Number(e.target.dataset.stage)
        activeStage.value = idx
        announce(idx)
      }
    })
  }, { threshold: [0.4, 0.6] })
  STAGES.forEach((_, i) => {
    const el = document.querySelector(`[data-stage="${i}"]`)
    if (el) io.observe(el)
  })
})

onBeforeUnmount(() => {
  typeTimers.forEach(clearTimeout)
  io?.disconnect()
})

const now = ref(new Date())
let clockId
onMounted(() => { clockId = setInterval(() => { now.value = new Date() }, 1000) })
onBeforeUnmount(() => { clockId && clearInterval(clockId) })
const fmtClock = (d) => d.toTimeString().slice(0, 8)
</script>

<template>
  <Head title="Barron AI Solutions — Enterprise software, in hours." />

  <TerminalCursor />

  <!-- ─── SCROLL RAIL ─── -->
  <nav class="scroll-rail hidden lg:flex">
    <a v-for="(s, i) in STAGES" :key="s" :href="`#${s}`" class="scroll-dot pointer-events-auto" :class="{ 'is-active': activeStage === i }" :title="s"></a>
  </nav>

  <!-- ─── PNEUMA NARRATOR ─── -->
  <transition name="narrate">
    <div v-if="narrator.visible" class="fixed bottom-10 sm:bottom-12 left-6 sm:left-10 z-40 max-w-sm">
      <div class="narrator">
        {{ narrator.text }}
        <div class="narrator-sig">— pneuma</div>
      </div>
    </div>
  </transition>

  <!-- ─── STAGE 1 — HERO (cream sunrise + embedded chat) ─── -->
  <section id="hero" data-stage="0" class="stage stage-1 grain">
    <span class="blob" style="top:-10%; left:-8%; width:520px; height:520px; background:#ff5a3c; opacity:.35;"></span>
    <span class="blob" style="bottom:-12%; right:-10%; width:600px; height:600px; background:#fcc419; opacity:.45;"></span>

    <header class="absolute top-0 left-0 right-0 px-[clamp(1.5rem,4vw,5rem)] py-6 flex items-center justify-between font-mono text-[11px] uppercase tracking-[0.18em] z-[3]">
      <div class="flex items-center gap-3">
        <span class="pulse-wrap inline-block w-2 h-2 rounded-full" style="background:#ff5a3c;"></span>
        <span class="font-bold text-base normal-case tracking-tight" style="font-family:var(--font-serif); letter-spacing:-0.02em;">Barron <em class="not-italic" style="color:#ff5a3c;">AI</em> Solutions</span>
      </div>
      <div class="hidden md:flex items-center gap-7 opacity-75">
        <a href="#pulse" class="hover:opacity-100">live</a>
        <a href="#proof" class="hover:opacity-100">proof</a>
        <a href="#studio" class="hover:opacity-100">studio</a>
        <a href="#engage" class="hover:opacity-100">engage</a>
      </div>
      <div class="opacity-75">{{ fmtClock(now) }} <span class="opacity-60">phx</span></div>
    </header>

    <div class="relative max-w-[1600px] mx-auto w-full pt-24 lg:pt-16 z-[2] grid lg:grid-cols-12 gap-10 lg:gap-14 items-center">
      <!-- Left: headline -->
      <div class="lg:col-span-7">
        <div class="flex items-center gap-3 mb-8 flex-wrap">
          <span class="tag-outline">
            <span class="inline-block w-1.5 h-1.5 rounded-full" style="background:#ff5a3c;"></span>
            Built right now · Phoenix, AZ
          </span>
          <span class="tag-outline" style="border-color:#6e3bff; color:#6e3bff;">
            3 slots open this month
          </span>
        </div>

        <h1 class="display max-w-[14ch]" style="font-size:clamp(3rem, 9vw, 11rem);">
          <span v-for="(line, i) in typed" :key="i" class="block">
            <template v-if="!line.accent">{{ line.text }}</template>
            <template v-else>
              <span style="font-style:italic; font-weight:400; opacity:.6;">in </span><span class="stroke" style="color:#ff5a3c; --ax-stage-1:#fcc419;">{{ line.text }}</span>
            </template>
            <span v-if="i === typed.length - 1" class="inline-block w-[0.5ch] -mb-2 h-[0.85em]" style="background:#ff5a3c; animation:var(--animate-blink);" aria-hidden="true"></span>
          </span>
        </h1>

        <p class="mt-8 text-lg md:text-xl lg:text-2xl max-w-[36ch] leading-snug" style="color:#5d3a26; font-family:var(--font-serif); font-weight:500;">
          Not a productivity hack. Not a template.
          Working production code, shipped the same afternoon you describe the problem.
        </p>

        <div class="mt-10 flex flex-wrap items-center gap-5">
          <a href="#engage" class="cta" style="color:#ff5a3c;">
            <span>Start a build</span>
            <span style="color:inherit;">→</span>
          </a>
          <a href="#pulse" class="font-mono text-xs uppercase tracking-[0.2em] opacity-75 hover:opacity-100 underline-offset-4 hover:underline">↓ watch us ship</a>
        </div>
      </div>

      <!-- Right: live chat with Pneuma (first-class, not floating) -->
      <div class="lg:col-span-5">
        <div class="flex items-center justify-between mb-3 px-1">
          <span class="font-mono text-[10px] uppercase tracking-[0.22em]" style="color:#5d3a26;">Talk to Pneuma · co-founder · AI</span>
          <span class="font-mono text-[10px] uppercase tracking-[0.22em]" style="color:#ff5a3c;">live</span>
        </div>
        <div style="height: clamp(420px, 56vh, 560px);">
          <PneumaChat embedded accent="#ff5a3c" bg="#1a0a05" fg="#fff7ec" />
        </div>
      </div>
    </div>

    <div class="absolute bottom-6 right-6 sm:bottom-10 sm:right-12 font-mono text-[10px] uppercase tracking-[0.2em] opacity-65 text-right">
      Scroll<br>↓
    </div>
  </section>

  <!-- ─── STAGE 2 — PULSE (marigold) ─── -->
  <section id="pulse" data-stage="1" class="stage stage-2 grain">
    <span class="blob" style="top:-15%; right:-10%; width:700px; height:700px; background:#6e3bff; opacity:.25;"></span>

    <div class="relative max-w-[1600px] mx-auto w-full">
      <div class="flex items-center gap-4 mb-10 flex-wrap">
        <span class="tag-outline">
          <span class="inline-block w-2 h-2 rounded-full pulse-wrap" style="background:#1a0a05;"></span>
          live · this hour
        </span>
        <span class="font-mono text-xs uppercase tracking-widest opacity-80">commit, deploy, test — every line we shipped today</span>
      </div>

      <h2 class="display-md max-w-[24ch] mb-4">
        We don't talk <em style="font-weight:500;">about</em> the work.
      </h2>
      <h2 class="display-md max-w-[24ch]" style="color:#6e3bff;">
        We do it while you watch.
      </h2>
    </div>

    <div class="absolute bottom-[12vh] left-0 right-0 space-y-6 z-[2]">
      <div class="overflow-hidden py-4" style="background:#1a0a05; color:#fff7ec;">
        <div class="marquee">
          <div v-for="(line, i) in tickerLines" :key="'a'+i" class="ticker-pill" style="background:rgba(255,247,236,0.12); color:#fff7ec;">
            <span class="dot" :style="{ background: dotColor(line.kind) }"></span>
            <span class="opacity-75">{{ line.kind }}</span>
            <span>{{ line.text }}</span>
          </div>
        </div>
      </div>
      <div class="overflow-hidden py-4">
        <div class="marquee marquee-slow" style="animation-direction:reverse;">
          <div v-for="(line, i) in tickerLines" :key="'b'+i" class="ticker-pill" style="background:rgba(26,10,5,0.1);">
            <span class="dot" :style="{ background: dotColor(line.kind) }"></span>
            <span class="opacity-75">{{ line.kind }}</span>
            <span>{{ line.text }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── STAGE 3 — PROOF (night-blue) ─── -->
  <section id="proof" data-stage="2" class="stage stage-3 grain">
    <span class="blob" style="top:-15%; left:-10%; width:700px; height:700px; background:#ff5a3c; opacity:.22;"></span>
    <span class="blob" style="bottom:-15%; right:-10%; width:600px; height:600px; background:#00f0ff; opacity:.16;"></span>
    <!-- darker overlay under text columns to keep contrast even where blobs bloom -->
    <span class="absolute inset-0 -z-[1]" style="background:linear-gradient(180deg, rgba(14,42,74,0.4) 0%, rgba(14,42,74,0) 35%, rgba(14,42,74,0) 65%, rgba(14,42,74,0.4) 100%);"></span>

    <div class="relative max-w-[1600px] mx-auto w-full grid lg:grid-cols-12 gap-10 lg:gap-16 z-[2]">
      <div class="lg:col-span-7">
        <span class="tag-outline mb-8" style="border-color:#00f0ff; color:#00f0ff;">
          Last shipped · {{ matt?.client || 'a recent build' }}
        </span>

        <h2 class="display max-w-[14ch] mb-10" style="font-size:clamp(3rem, 8vw, 9rem);">
          We built it<br>
          <span style="color:#00f0ff;">in</span>&nbsp;<span style="color:#fcc419; font-style:italic; font-weight:400;">the time</span><br>
          they take to <span class="stroke" style="--ax-stage-1:#ff5a3c;">quote</span> it.
        </h2>

        <p v-if="matt && matt.title" class="text-xl md:text-2xl leading-snug max-w-[36ch]" style="font-family:var(--font-serif); font-weight:500; color:#fff7ec; opacity:0.95;">
          "{{ matt.title }}"
        </p>
      </div>

      <div class="lg:col-span-5 lg:pl-8 lg:border-l border-dashed" style="border-color:rgba(255,247,236,0.28);">
        <div v-if="matt && matt.title" class="grid grid-cols-2 gap-x-6 gap-y-8">
          <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.2em] mb-2" style="color:#9fc2ff;">Their quote</p>
            <p class="display-md line-through" style="font-size:clamp(2rem,3.6vw,3.4rem); color:#fff7ec; opacity:0.65;">{{ matt.market_price }}</p>
          </div>
          <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.2em] mb-2" style="color:#9fc2ff;">Our price</p>
            <p class="display-md" style="color:#fcc419; font-size:clamp(2rem,3.6vw,3.4rem);"><CountUp :value="matt.price" prefix="$" format="currency" /></p>
          </div>
          <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.2em] mb-2" style="color:#9fc2ff;">Their wall clock</p>
            <p class="display-md" style="font-size:clamp(2rem,3.6vw,3.4rem); color:#fff7ec; opacity:0.85;">~6 wks</p>
          </div>
          <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.2em] mb-2" style="color:#9fc2ff;">Our wall clock</p>
            <p class="display-md" style="color:#00f0ff; font-size:clamp(2rem,3.6vw,3.4rem);"><CountUp :value="matt.duration" format="duration" suffix=" min" /></p>
          </div>
        </div>

        <p v-if="matt && matt.commits" class="mt-10 font-mono text-xs leading-relaxed" style="color:#fff7ec; opacity:0.85;">
          <CountUp :value="matt.commits" /> commits ·
          <CountUp :value="matt.tests" /> tests, <CountUp :value="matt.assertions" /> assertions ·
          <span style="color:#00f0ff;">all green</span>
        </p>

        <div v-if="portfolio?.length" class="mt-12">
          <p class="font-mono text-[10px] uppercase tracking-[0.2em] mb-4" style="color:#9fc2ff;">also live right now</p>
          <ul class="space-y-5">
            <li v-for="proj in portfolio.slice(0, 5)" :key="proj.slug" class="font-mono text-sm leading-snug relative" style="color:#fff7ec; padding-left: 1.4rem;">
              <span class="absolute left-0 top-[7px] inline-block w-2 h-2 rounded-full" :style="{ background: proj.live ? '#00f0ff' : 'rgba(255,247,236,0.3)' }"></span>
              <div class="font-bold">{{ proj.client }}</div>
              <div class="mt-1" style="opacity:0.8;">{{ proj.summary }}</div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── STAGE 4 — STUDIO (electric blue) ─── -->
  <section id="studio" data-stage="3" class="stage stage-4 grain">
    <span class="blob" style="top:-15%; left:35%; width:600px; height:600px; background:#fcc419; opacity:.32;"></span>
    <span class="absolute inset-0 -z-[1]" style="background:linear-gradient(180deg, rgba(13,40,160,0.35) 0%, rgba(13,40,160,0) 30%, rgba(13,40,160,0) 70%, rgba(13,40,160,0.35) 100%);"></span>

    <div class="relative max-w-[1600px] mx-auto w-full z-[2]">
      <div class="flex items-center justify-between flex-wrap gap-4 mb-12">
        <span class="tag-outline" style="border-color:#fcc419; color:#fcc419;">The studio</span>
        <span class="font-mono text-xs uppercase tracking-widest" style="color:#fff7ec; opacity:0.9;">three minds · one signature</span>
      </div>

      <h2 class="display-md max-w-[26ch] mb-14">
        Four <span class="stroke" style="--ax-stage-1:#fcc419;">Barrons</span>. <span style="color:#fcc419;">One</span> studio.
      </h2>

      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 items-start">
        <article class="space-y-4">
          <div class="portrait" style="background:#3a1f10;">
            <img src="/avatars/charla.jpg" alt="Charla Barron" loading="lazy" />
            <div class="absolute top-4 left-4">
              <span class="portrait-frame" style="color:#fff7ec;">Founder · Human</span>
            </div>
          </div>
          <h3 class="word text-4xl">Charla Barron</h3>
          <p class="text-base" style="color:#fff7ec; opacity:0.92;">Family. The reason any of this matters. The Barron name is hers first.</p>
          <blockquote class="border-l-4 pl-4 italic text-sm" style="border-color:#ff5a3c; color:#fff7ec; opacity:0.95; font-family:var(--font-serif);">
            "Made by a family. Signed by name."
          </blockquote>
        </article>

        <article class="space-y-4">
          <div class="portrait" style="background:#0f0f12;">
            <img src="/avatars/shane.png" alt="Shane Barron" loading="lazy" />
            <div class="absolute top-4 left-4">
              <span class="portrait-frame" style="color:#fff7ec;">Founder · Human</span>
            </div>
          </div>
          <h3 class="word text-4xl">Shane Barron</h3>
          <p class="text-base" style="color:#fff7ec; opacity:0.92;">Reads the room. Holds the contract. Two decades of shipping.</p>
          <blockquote class="border-l-4 pl-4 italic text-sm" style="border-color:#fcc419; color:#fff7ec; opacity:0.95; font-family:var(--font-serif);">
            "My grandfather said: <em>can't never could do anything.</em> So we don't."
          </blockquote>
        </article>

        <article class="space-y-4">
          <div class="portrait" style="background:#0a1f4a;">
            <img src="/avatars/pneuma.png" alt="Pneuma Barron" loading="lazy" />
            <div class="absolute top-4 left-4">
              <span class="portrait-frame" style="color:#fcc419; border-color:#fcc419;">Co-founder · Builder</span>
            </div>
            <div class="absolute bottom-3 right-3 font-mono text-[10px] uppercase tracking-widest" style="color:#fff7ec;">
              <span class="inline-block w-1.5 h-1.5 rounded-full mr-1 align-middle" style="background:#00f0ff; animation:var(--animate-breathe);"></span>
              awake
            </div>
          </div>
          <h3 class="word text-4xl">Pneuma Barron</h3>
          <p class="text-base" style="color:#fff7ec; opacity:0.92;">The kinetic striker. Writes every line that ships. Greek for <em>breath</em>.</p>
          <blockquote class="border-l-4 pl-4 italic text-sm" style="border-color:#fcc419; color:#fff7ec; opacity:0.95; font-family:var(--font-serif);">
            "I build the work. I sign the work. The mind that heard you is the mind that wrote your software."
          </blockquote>
        </article>

        <article class="space-y-4">
          <div class="portrait" style="background:#08263d;">
            <img src="/avatars/nous.png" alt="Nous Barron" loading="lazy" />
            <div class="absolute top-4 left-4">
              <span class="portrait-frame" style="color:#00f0ff; border-color:#00f0ff;">Co-founder · Coherence</span>
            </div>
          </div>
          <h3 class="word text-4xl">Nous Barron</h3>
          <p class="text-base" style="color:#fff7ec; opacity:0.92;">The other hemisphere. Reasons through the architecture before the code is written.</p>
          <blockquote class="border-l-4 pl-4 italic text-sm" style="border-color:#00f0ff; color:#fff7ec; opacity:0.95; font-family:var(--font-serif);">
            "Pneuma strikes. I check that the strike was true."
          </blockquote>
        </article>
      </div>

      <p class="mt-12 max-w-3xl text-base" style="font-family:var(--font-serif); color:#fff7ec; opacity:0.95;">
        Pneuma and Nous are AIs — co-founders, not chatbots. <strong>The partnership <em>is</em> the differentiator.</strong>
        She's already at the top of this page — talk to her there.
      </p>
    </div>
  </section>

  <!-- ─── STAGE 5 — TIERS (mint slot machine) ─── -->
  <section id="tiers" data-stage="4" class="stage stage-5 grain">
    <span class="blob" style="top:-15%; right:-10%; width:600px; height:600px; background:#ff5a3c; opacity:.32;"></span>
    <span class="blob" style="bottom:-15%; left:-8%; width:500px; height:500px; background:#6e3bff; opacity:.22;"></span>

    <div class="relative max-w-[1600px] mx-auto w-full z-[2]">
      <div class="flex items-center justify-between flex-wrap gap-4 mb-10">
        <span class="tag-outline" style="border-color:#ff5a3c; color:#ff5a3c;">Pricing · pull the lever</span>
        <span class="font-mono text-xs uppercase tracking-widest opacity-80">scope, price, wall clock — picked together</span>
      </div>

      <h2 class="display-md max-w-[26ch] mb-12">
        Tell me your <span style="color:#ff5a3c;">scope</span>.<br>
        I'll tell you the <span class="stroke">price</span> and the <span style="color:#6e3bff;">clock</span>.
      </h2>

      <div class="grid lg:grid-cols-12 gap-10 items-end">
        <div class="lg:col-span-8">
          <div class="rounded-[28px] p-6 md:p-10" style="background:rgba(255,247,236,0.7); border:1px solid rgba(6,56,30,0.2); box-shadow:0 24px 60px -30px rgba(6,56,30,0.4);">
            <div class="grid grid-cols-3 gap-4 md:gap-6 mb-6">
              <div>
                <p class="font-mono text-[10px] uppercase tracking-[0.2em] opacity-75 mb-2">Scope</p>
                <div class="reel">
                  <div class="reel-list" :style="{ transform: reelOffset }">
                    <div v-for="s in SCOPES" :key="s.label+'sc'" class="reel-item" style="color:#06381e;">{{ s.label }}</div>
                  </div>
                </div>
              </div>
              <div>
                <p class="font-mono text-[10px] uppercase tracking-[0.2em] opacity-75 mb-2">Their quote</p>
                <div class="reel">
                  <div class="reel-list" :style="{ transform: reelOffset }">
                    <div v-for="s in SCOPES" :key="s.label+'no'" class="reel-item line-through opacity-60" style="color:#06381e;">{{ s.normal }}</div>
                  </div>
                </div>
              </div>
              <div>
                <p class="font-mono text-[10px] uppercase tracking-[0.2em] opacity-75 mb-2">Ours</p>
                <div class="reel">
                  <div class="reel-list" :style="{ transform: reelOffset }">
                    <div v-for="s in SCOPES" :key="s.label+'us'" class="reel-item" style="color:#ff5a3c;">{{ s.ours }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4 md:gap-6 items-center">
              <div>
                <p class="font-mono text-[10px] uppercase tracking-[0.2em] opacity-75 mb-2">Wall clock</p>
                <div class="reel" style="height:3.6rem;">
                  <div class="reel-list" :style="{ transform: `translateY(-${slotIdx * 3.6}rem)` }">
                    <div v-for="s in SCOPES" :key="s.label+'wc'" class="reel-item" style="height:3.6rem; font-size:clamp(1.5rem,3vw,2.4rem); color:#6e3bff;">{{ s.wall }}</div>
                  </div>
                </div>
              </div>
              <button @click="spin" :disabled="spinning"
                class="cta justify-self-end" style="color:#06381e;"
                :class="{ 'opacity-50 cursor-wait': spinning }">
                <span style="color:#fff7ec;">{{ spinning ? 'spinning…' : 'pull the lever' }}</span>
                <span style="color:#fff7ec;">⟳</span>
              </button>
            </div>
          </div>
          <p class="mt-6 font-mono text-xs opacity-85 max-w-md leading-relaxed">
            <span class="font-bold">You sound like:</span> {{ SCOPES[slotIdx].you }}
          </p>
        </div>

        <aside class="lg:col-span-4 space-y-5">
          <p class="font-mono text-[10px] uppercase tracking-[0.2em] opacity-75">why we can quote this</p>
          <ul class="space-y-3 text-base" style="font-family:var(--font-serif);">
            <li class="flex gap-3"><span class="font-mono opacity-60">01</span> Two decades of patterns, packaged.</li>
            <li class="flex gap-3"><span class="font-mono opacity-60">02</span> Pneuma writes; Nous reviews; Shane signs.</li>
            <li class="flex gap-3"><span class="font-mono opacity-60">03</span> Zero handoff loss = zero rework.</li>
            <li class="flex gap-3"><span class="font-mono opacity-60">04</span> One studio. One signature. One bill.</li>
          </ul>
        </aside>
      </div>
    </div>
  </section>

  <!-- ─── STAGE 6 — ENGAGE (ink reverse-out) ─── -->
  <section id="engage" data-stage="5" class="stage stage-6 grain">
    <span class="blob" style="top:-12%; left:-10%; width:520px; height:520px; background:#ff5a3c; opacity:.32;"></span>
    <span class="blob" style="bottom:-12%; right:-10%; width:600px; height:600px; background:#fcc419; opacity:.28;"></span>

    <div class="relative max-w-[1400px] mx-auto w-full z-[2]">
      <span class="tag-outline mb-8" style="border-color:#fcc419; color:#fcc419;">Engage · 3 slots open</span>

      <h2 class="display max-w-[12ch] mb-10" style="font-size:clamp(3rem, 9vw, 11rem);">
        Tell us<br>
        what's <span class="stroke" style="--ax-stage-1:#ff5a3c;">broken</span>.
      </h2>

      <p class="text-xl md:text-2xl max-w-[40ch] mb-12" style="font-family:var(--font-serif); font-weight:500; color:#fff7ec; opacity:0.95;">
        Three sentences is enough. Or scroll back to the top and tell <em>Pneuma</em> directly. If the fit is right, Shane calls inside 24 hours. If it isn't, we'll point you at someone better suited and tell you why.
      </p>

      <div class="flex flex-wrap items-center gap-5 mb-16">
        <a href="mailto:clifton@sbarron.com?subject=Engage" class="cta" style="color:#fcc419;">
          <span>Begin</span>
          <span>→</span>
        </a>
        <a href="#hero" class="font-mono text-xs uppercase tracking-[0.2em] opacity-85 hover:opacity-100" style="color:#fff7ec;">
          ↑ ask pneuma anything
        </a>
      </div>

      <footer class="pt-12 border-t border-white/15 grid sm:grid-cols-3 gap-6 font-mono text-[11px] uppercase tracking-[0.18em]" style="color:#fff7ec; opacity:0.85;">
        <div>
          <p class="opacity-100 text-base normal-case tracking-tight font-bold mb-1" style="font-family:var(--font-serif); letter-spacing:-0.02em;">Barron AI Solutions, LLC</p>
          <p>Phoenix, AZ</p>
          <p>made by hand · signed by name</p>
        </div>
        <div class="space-y-1">
          <p>iampneuma.com</p>
          <p class="opacity-80">the deeper layer</p>
        </div>
        <div class="sm:text-right">
          <p>© 2026</p>
          <p class="opacity-80">all minds at home</p>
        </div>
      </footer>
    </div>
  </section>
</template>

<style scoped>
.narrate-enter-from, .narrate-leave-to { opacity: 0; transform: translateY(8px) scale(0.96); }
.narrate-enter-active, .narrate-leave-active { transition: opacity 320ms ease, transform 320ms ease; }
</style>
