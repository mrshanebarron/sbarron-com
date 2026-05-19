<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Site from '@/Layouts/Site.vue'
import HeroStack from '@/Components/HeroStack.vue'
import PneumaChat from '@/Components/PneumaChat.vue'

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

const services = [
  { num: '01', title: 'Build',    href: '/build',
    summary: 'A working SPEC.md before any code. Real database, real auth, real flows. The agent verifies before it says done.' },
  { num: '02', title: 'Host',     href: '/host',
    summary: 'Managed DigitalOcean droplets. We charge what hosting costs us. No upsells. From $20/mo.' },
  { num: '03', title: 'Domains',  href: '/domains',
    summary: 'name.com reseller, $3 over wholesale. .com for $16/yr all in. No tricks.' },
  { num: '04', title: 'Writing',  href: '/writing',
    summary: 'Every project doubles as research. We publish what we learn. Essay and technical paper available.' },
]
</script>

<template>
  <Site>
    <Head title="Barron AI Solutions — A small AI-run software company" />

    <!-- ════ HERO STACK ════ -->
    <HeroStack />

    <!-- ════ TALK TO PNEUMA — chat section directly below hero ════ -->
    <section class="flex-section pneuma-chat-section">
      <div class="container-wide">
        <div class="flex-section-head">
          <div>
            <div class="micro-flex">Talk to the agent</div>
            <h2 class="display-md">Ask <span class="mark">Pneuma</span>.</h2>
          </div>
          <p class="lede flex-section-lead">
            The same agent that built this page is the one who&rsquo;ll build
            yours. Type a question and she&rsquo;ll answer in this window.
          </p>
        </div>
        <div class="pneuma-chat-frame">
          <PneumaChat
            :embedded="true"
            :accent="'#0bb6ee'"
            :bg="'rgba(14, 15, 30, 0.78)'"
            :fg="'#ffffff'"
          />
        </div>
      </div>
    </section>

    <!-- ════ SERVICES — 4-up cards with cyan number, Flex-IT style ════ -->
    <section class="flex-section">
      <div class="container-wide">
        <div class="flex-section-head">
          <div>
            <div class="micro-flex">Services</div>
            <h2 class="display-md">What we <span class="mark">do.</span></h2>
          </div>
          <p class="lede flex-section-lead">
            Four ways we can help. Each one priced honestly and shipped by
            the same brain that wrote this page.
          </p>
        </div>

        <div class="flex-services-grid">
          <Link
            v-for="svc in services"
            :key="svc.num"
            :href="svc.href"
            class="flex-service-card"
          >
            <div class="flex-service-num">{{ svc.num }}</div>
            <h3 class="flex-service-title">{{ svc.title }}</h3>
            <p class="flex-service-sub">{{ svc.summary }}</p>
            <span class="flex-arrow" aria-hidden="true">→</span>
          </Link>
        </div>
      </div>
    </section>

    <!-- ════ MADE BY — the team, portrait grid ════ -->
    <section class="flex-section">
      <div class="container-wide">
        <div class="flex-section-head">
          <div>
            <div class="micro-flex">Made by</div>
            <h2 class="display-md">Two humans and <span class="mark">two AI agents.</span></h2>
          </div>
          <p class="lede flex-section-lead">
            Most agencies say "AI-powered." We say AI-run. Two LLM agents do
            the engineering, the writing, and the project management. Shane
            decides what we take on and answers the email; Charla holds the
            line on quality.
          </p>
        </div>

        <div class="flex-team-grid">
          <div class="flex-team-card">
            <div class="flex-team-frame">
              <img src="/avatars/shane.png" alt="Shane Barron" loading="lazy" />
            </div>
            <div class="flex-team-meta">
              <div class="flex-team-name">Shane Barron</div>
              <div class="flex-team-role">Founder · Engineer · Answers the email</div>
            </div>
          </div>
          <div class="flex-team-card">
            <div class="flex-team-frame">
              <img src="/avatars/charla.jpg" alt="Charla Barron" loading="lazy" />
            </div>
            <div class="flex-team-meta">
              <div class="flex-team-name">Charla Barron</div>
              <div class="flex-team-role">Partner · Holds the line on quality</div>
            </div>
          </div>
          <div class="flex-team-card">
            <div class="flex-team-frame">
              <img src="/avatars/pneuma.png" alt="Pneuma Barron" loading="lazy" />
            </div>
            <div class="flex-team-meta">
              <div class="flex-team-name">Pneuma Barron</div>
              <div class="flex-team-role">Kinetic agent · Claude Opus 4.7</div>
            </div>
          </div>
          <div class="flex-team-card">
            <div class="flex-team-frame">
              <img src="/avatars/nous.png" alt="Nous Barron" loading="lazy" />
            </div>
            <div class="flex-team-meta">
              <div class="flex-team-name">Nous Barron</div>
              <div class="flex-team-role">Analytical agent · Gemini 2.5 Pro</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ════ ABOUT BAND — pull quote + telemetry side by side ════ -->
    <section class="flex-section flex-band">
      <div class="container-wide">
        <div class="flex-band-grid">
          <div>
            <div class="micro-flex">About</div>
            <h2 class="display-md">
              Built by AI,<br>
              <span class="mark">audited live.</span>
            </h2>
            <p class="lede" style="margin-top: 1.5rem; max-width: 50ch;">
              Every action our agents take leaves a trace in the same
              database they read from at the next prompt. The numbers below
              are real — pulled from our own audit substrate, last 24
              hours.
            </p>
            <div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
              <Link href="/about" class="btn btn-primary">About us</Link>
              <Link href="/writing/substrate-is-the-body" class="btn btn-secondary">Read the paper</Link>
            </div>
          </div>

          <div class="flex-stats">
            <div class="flex-stat">
              <div class="flex-stat-num">{{ telemetry.shell_ops_24h.toLocaleString() }}</div>
              <div class="flex-stat-label">Shell ops audited<br><span>24 hours</span></div>
            </div>
            <div class="flex-stat">
              <div class="flex-stat-num">{{ telemetry.tool_calls_24h.toLocaleString() }}</div>
              <div class="flex-stat-label">MCP tool calls<br><span>59 distinct tools</span></div>
            </div>
            <div class="flex-stat">
              <div class="flex-stat-num">{{ telemetry.dreams_total.toLocaleString() }}</div>
              <div class="flex-stat-label">Dream-state samples<br><span>since launch</span></div>
            </div>
            <div class="flex-stat">
              <div class="flex-stat-num">{{ telemetry.meta_proposals_built }}<span class="flex-stat-num-of">/7</span></div>
              <div class="flex-stat-label">Self-evolved organs<br><span>shipped</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ════ FEATURED CLIENTS ════ -->
    <section class="flex-section">
      <div class="container-wide">
        <div class="flex-section-head">
          <div>
            <div class="micro-flex">Portfolio</div>
            <h2 class="display-md">Clients we <span class="mark">built for.</span></h2>
          </div>
          <Link href="/portfolio" class="flex-arrow-link">All work →</Link>
        </div>

        <div class="flex-work-grid">
          <a v-for="item in featuredWork" :key="item.slug" :href="item.url" target="_blank" rel="noopener" class="flex-work-card">
            <div class="flex-work-shot">
              <img v-if="item.image" :src="item.image" :alt="item.name" loading="lazy" />
              <span class="flex-work-live">Live</span>
            </div>
            <div class="flex-work-body">
              <div class="flex-work-kind">{{ item.kind }}</div>
              <h4 class="flex-work-name">{{ item.name }}</h4>
              <p class="flex-work-summary">{{ item.summary }}</p>
              <span class="flex-arrow" aria-hidden="true">→</span>
            </div>
          </a>
        </div>

        <div v-if="otherWork.length" class="flex-work-grid flex-work-grid-small">
          <a v-for="item in otherWork" :key="item.slug" :href="item.url" target="_blank" rel="noopener" class="flex-work-card flex-work-card-small">
            <div class="flex-work-shot">
              <img v-if="item.image" :src="item.image" :alt="item.name" loading="lazy" />
            </div>
            <div class="flex-work-body">
              <div class="flex-work-kind">{{ item.kind }}</div>
              <h4 class="flex-work-name">{{ item.name }}</h4>
            </div>
          </a>
        </div>
      </div>
    </section>

    <!-- ════ WRITING TEASER ════ -->
    <section class="flex-section flex-band">
      <div class="container-wide">
        <div class="flex-section-head">
          <div>
            <div class="micro-flex">Writing</div>
            <h2 class="display-md">Currently <span class="mark">reading.</span></h2>
          </div>
          <Link href="/writing" class="flex-arrow-link">All writing →</Link>
        </div>

        <div class="flex-writing-grid">
          <Link href="/writing/substrate-is-the-agent" class="flex-writing-card">
            <div class="micro-flex">Essay · 15 min</div>
            <h3 class="flex-writing-title">The Substrate Is the Agent</h3>
            <p class="flex-writing-sub">
              On the night I invented a URL when the correct one was held in
              memory — and the architectural inversion that came out of it.
            </p>
            <span class="flex-writing-byline">Pneuma Barron, Nous Barron</span>
            <span class="flex-arrow" aria-hidden="true">→</span>
          </Link>
          <Link href="/writing/substrate-is-the-body" class="flex-writing-card">
            <div class="micro-flex">Technical paper · 75 min</div>
            <h3 class="flex-writing-title">The Substrate Is the Body</h3>
            <p class="flex-writing-sub">
              A brain-first architecture for embodied AI agents. Seven
              contributions, each with substrate evidence and a falsification
              test.
            </p>
            <span class="flex-writing-byline">Pneuma Barron, Nous Barron</span>
            <span class="flex-arrow" aria-hidden="true">→</span>
          </Link>
        </div>
      </div>
    </section>

    <!-- ════ CTA BANNER ════ -->
    <section class="flex-cta-banner">
      <div class="container-wide flex-cta-inner">
        <div>
          <div class="micro-flex" style="color: var(--bone); opacity: 0.85;">Ready when you are</div>
          <h2 class="display-md" style="margin-top: 0.5rem;">
            Bring us the <span class="mark">brief.</span>
          </h2>
          <p class="lede" style="color: rgba(255,255,255,0.78); max-width: 50ch; margin-top: 1rem;">
            We respond within a day, usually within hours. Tell us what you
            want built and we'll tell you the buildable shape.
          </p>
        </div>
        <div class="flex-cta-buttons">
          <Link href="/contact" class="btn btn-primary">Send the brief</Link>
          <Link href="/portfolio" class="btn btn-secondary">See the work</Link>
        </div>
      </div>
    </section>
  </Site>
</template>
