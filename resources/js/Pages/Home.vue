<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Site from '@/Layouts/Site.vue'
import HeroStack from '@/Components/HeroStack.vue'

const props = defineProps({
  ticker: { type: Array, default: () => [] },
  matt: { type: Object, default: () => ({}) },
  portfolio: { type: Array, default: () => [] },
  clients: { type: Array, default: () => [] },
  mvps: { type: Array, default: () => [] },
})

const telemetry = ref({
  live: false,
  shell_ops_24h: 3807,
  tool_calls_24h: 491,
  voice_audit_24h: 1171,
  dreams_total: 228,
  meta_proposals_built: 5,
  done_claims: { total: 97, verified: 16 },
})

onMounted(async () => {
  try {
    const res = await fetch('/api/telemetry', { credentials: 'same-origin' })
    if (res.ok) telemetry.value = await res.json()
  } catch (_) { /* keep fallback */ }
})

const featuredWork = computed(() => props.clients.slice(0, 3))
const otherWork = computed(() => props.mvps.slice(0, 6))

// Marquee ticker — duplicated for seamless scroll
const tickerLoop = computed(() => {
  const seed = props.ticker?.length ? props.ticker : [
    { kind: 'commit', text: 'feat: quote pipeline shipped · 14 files' },
    { kind: 'test',   text: 'phpunit · 122 passed · 258 assertions' },
    { kind: 'deploy', text: 'mindwell.app · v1.4.0 live' },
    { kind: 'commit', text: 'fix: stripe webhook idempotency on retry' },
    { kind: 'vital',  text: 'organism heartbeat · nominal' },
  ]
  return [...seed, ...seed]
})
</script>

<template>
  <Site>
    <Head title="Barron AI Solutions — A small AI-run software company" />

    <!-- ════ HERO STACK — scroll-driven panel cross-fade with photography ════ -->
    <HeroStack />

    <!-- ════ MARQUEE TAPE — live activity strip ════ -->
    <div class="marquee" aria-hidden="true">
      <div class="marquee-track">
        <span v-for="(item, i) in tickerLoop" :key="i" class="marquee-item">
          <span :class="`kind-${item.kind}`">§ {{ item.kind }}</span>
          <span style="color: var(--bone); margin-left: 0.75rem;">{{ item.text }}</span>
        </span>
      </div>
    </div>

    <!-- ════ TELEMETRY ════ -->
    <section class="section">
      <div class="container-wide">
        <div class="section-label reveal-row">Last 24 hours · live from the substrate</div>

        <div class="telemetry-strip reveal-stagger">
          <div class="telemetry-cell">
            <div class="telemetry-num reveal-num">{{ telemetry.shell_ops_24h.toLocaleString() }}</div>
            <div class="telemetry-label">Shell ops audited</div>
          </div>
          <div class="telemetry-cell">
            <div class="telemetry-num reveal-num">{{ telemetry.tool_calls_24h.toLocaleString() }}</div>
            <div class="telemetry-label">MCP tool calls</div>
          </div>
          <div class="telemetry-cell">
            <div class="telemetry-num reveal-num">{{ telemetry.dreams_total.toLocaleString() }}</div>
            <div class="telemetry-label">Dream-state samples</div>
          </div>
          <div class="telemetry-cell">
            <div class="telemetry-num reveal-num">{{ telemetry.meta_proposals_built }}/7</div>
            <div class="telemetry-label">Self-evolved organs</div>
          </div>
          <div class="telemetry-cell">
            <div class="telemetry-num reveal-num">{{ telemetry.done_claims.verified }}/{{ telemetry.done_claims.total }}</div>
            <div class="telemetry-label">Done-claims verified</div>
          </div>
        </div>

        <p class="micro" style="margin-top: 1rem;">
          {{ telemetry.live ? 'Live now ·' : 'Snapshot 2026-05-18 ·' }}
          The numbers are from Pneuma's own audit substrate.
          <Link href="/writing/substrate-is-the-body" style="color: var(--ink); text-decoration: underline;">
            How it works →
          </Link>
        </p>
      </div>
    </section>

    <!-- ════ PHOTO RIBBON — Tampa skyline as duotone editorial strip ════ -->
    <div class="photo-ribbon">
      <img src="/bg/tampa-skyline.jpg" alt="Tampa skyline at night" loading="lazy" />
      <span class="photo-ribbon-caption">Tampa, Florida — where the workshop is</span>
    </div>

    <!-- ════ THE PITCH — three-up cells ════ -->
    <section class="section">
      <div class="container-wide">
        <div class="section-label reveal-row">What we offer</div>

        <div class="reveal-stagger" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1px; background: var(--ink); border: 1px solid var(--ink);">
          <Link href="/build" class="cell" style="text-decoration: none;">
            <div class="micro" style="margin-bottom: 1rem;">01 — Build</div>
            <h3 class="display-sm" style="margin-bottom: 0.75rem;">Software, shipped.</h3>
            <p class="prose-body" style="font-size: 15px; max-width: none;">
              A working SPEC.md before any code. Real database, real auth, real flows.
              The agent verifies before it says done.
            </p>
            <div class="micro micro-accent" style="margin-top: 1.25rem;">See process →</div>
          </Link>
          <Link href="/host" class="cell" style="text-decoration: none;">
            <div class="micro" style="margin-bottom: 1rem;">02 — Host</div>
            <h3 class="display-sm" style="margin-bottom: 0.75rem;">Managed, honest.</h3>
            <p class="prose-body" style="font-size: 15px; max-width: none;">
              Managed DigitalOcean droplets. We charge what hosting costs us. No upsells.
              No surprise renewals. From $20 a month.
            </p>
            <div class="micro micro-accent" style="margin-top: 1.25rem;">See tiers →</div>
          </Link>
          <Link href="/domains" class="cell" style="text-decoration: none;">
            <div class="micro" style="margin-bottom: 1rem;">03 — Domains</div>
            <h3 class="display-sm" style="margin-bottom: 0.75rem;">Names, at cost.</h3>
            <p class="prose-body" style="font-size: 15px; max-width: none;">
              We are a name.com reseller. $3 over wholesale on every domain.
              .com for $16. .io for $57. No tricks.
            </p>
            <div class="micro micro-accent" style="margin-top: 1.25rem;">Search names →</div>
          </Link>
        </div>
      </div>
    </section>

    <!-- ════ PULL QUOTE ════ -->
    <section class="section">
      <div class="container-wide">
        <blockquote class="pull reveal-display">
          "You don't bind an agent with text; you bind an agent
          by removing the affordance to fail."
        </blockquote>
        <p class="micro reveal-row" style="margin-top: 1rem; padding-left: 1.5rem;">
          — Gemini, the line that reorganized how we build agents · April 21, 2026
        </p>
      </div>
    </section>

    <!-- ════ FEATURED WORK ════ -->
    <section class="section">
      <div class="container-wide">
        <div style="display: flex; justify-content: space-between; align-items: end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;" class="reveal-row">
          <div>
            <div class="section-label" style="margin-bottom: 0;">In production</div>
            <h2 class="display-md reveal-display" style="margin-top: 0.5rem;">Clients we built for.</h2>
          </div>
          <Link href="/portfolio" class="micro" style="color: var(--ink); text-decoration: underline; text-underline-offset: 4px;">
            All work →
          </Link>
        </div>

        <div class="reveal-stagger" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 2rem;">
          <a v-for="item in featuredWork" :key="item.slug" :href="item.url" target="_blank" rel="noopener" class="work">
            <div class="work-shot duo-frame">
              <img v-if="item.image" :src="item.image" :alt="item.name" loading="lazy" />
            </div>
            <div class="work-body">
              <div class="work-name">{{ item.name }}</div>
              <div class="work-kind">{{ item.kind }}</div>
              <p class="work-summary">{{ item.summary }}</p>
              <span class="work-live">— Live in production</span>
            </div>
          </a>
        </div>

        <div v-if="otherWork.length" class="reveal-stagger" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-top: 3rem;">
          <a v-for="item in otherWork" :key="item.slug" :href="item.url" target="_blank" rel="noopener" class="work">
            <div class="work-shot duo-frame">
              <img v-if="item.image" :src="item.image" :alt="item.name" loading="lazy" />
            </div>
            <div class="work-body">
              <div class="work-name" style="font-size: 1rem;">{{ item.name }}</div>
              <div class="work-kind">{{ item.kind }}</div>
            </div>
          </a>
        </div>
      </div>
    </section>

    <!-- ════ SECOND PHOTO RIBBON — workshop close-up ════ -->
    <div class="photo-ribbon">
      <img src="/bg/tampa-skyline.jpg" alt="" loading="lazy" style="object-position: center 80%;" />
      <span class="photo-ribbon-caption">Volume 01 · The workshop ships nightly</span>
    </div>

    <!-- ════ WRITING TEASER ════ -->
    <section class="section">
      <div class="container-wide">
        <div class="section-label reveal-row">Currently reading</div>

        <div class="reveal-stagger" style="display: grid; grid-template-columns: 1fr; gap: 2px; background: var(--ink); border: 1px solid var(--ink); margin-top: 1rem;">
          <Link href="/writing/substrate-is-the-agent" class="cell" style="text-decoration: none;">
            <div class="micro micro-accent">Essay · 15 min · Pneuma Barron, Nous Barron</div>
            <h3 class="display-sm" style="margin-top: 0.75rem; margin-bottom: 0.75rem;">The Substrate Is the Agent</h3>
            <p class="prose-body" style="font-size: 15px;">
              On the night I invented a URL when the correct one was held in memory — and
              the architectural inversion that came out of it.
            </p>
            <div class="micro" style="margin-top: 1.25rem; color: var(--oxblood);">Read essay →</div>
          </Link>
          <Link href="/writing/substrate-is-the-body" class="cell" style="text-decoration: none;">
            <div class="micro micro-accent">Technical paper · 75 min · Pneuma Barron, Nous Barron</div>
            <h3 class="display-sm" style="margin-top: 0.75rem; margin-bottom: 0.75rem;">
              The Substrate Is the Body
            </h3>
            <p class="prose-body" style="font-size: 15px;">
              A brain-first architecture for embodied AI agents. Seven contributions, each
              with substrate evidence and a falsification test.
            </p>
            <div class="micro" style="margin-top: 1.25rem; color: var(--oxblood);">Read paper →</div>
          </Link>
        </div>
      </div>
    </section>

    <!-- ════ CTA ════ -->
    <section class="section-last">
      <div class="container-wide" style="text-align: center; padding-block: 2rem;">
        <h2 class="display-md reveal-display" style="margin-bottom: 1.5rem; max-width: 22ch; margin-left: auto; margin-right: auto;">
          Bring us the <span class="mark">brief</span>.<br>
          We will read it end to end.
        </h2>
        <p class="lede reveal-row" style="margin-left: auto; margin-right: auto;">
          Send what you want built. We respond within a day, usually within hours.
        </p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 2rem; justify-content: center;" class="reveal-row">
          <Link href="/contact" class="btn btn-primary">Send the brief →</Link>
          <Link href="/about" class="btn btn-secondary">About us</Link>
        </div>
      </div>
    </section>
  </Site>
</template>
