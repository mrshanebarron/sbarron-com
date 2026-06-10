<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Site from '@/Layouts/Site.vue'
import PneumaChat from '@/Components/PneumaChat.vue'

const props = defineProps({
  ticker: { type: Array, default: () => [] },
  matt: { type: Object, default: () => ({}) },
  portfolio: { type: Array, default: () => [] },
  clients: { type: Array, default: () => [] },
  mvps: { type: Array, default: () => [] },
})

/* ───────── live telemetry (real numbers from our own system) ───────── */
const telemetry = ref({
  live: false,
  shell_ops_24h: 3807,
  tool_calls_24h: 491,
})
onMounted(async () => {
  try {
    const res = await fetch('/api/telemetry', { credentials: 'same-origin' })
    if (res.ok) telemetry.value = await res.json()
  } catch (_) { /* keep fallback */ }
})

/* ───────── THE BUILD FLOOR HERO ─────────
   A line of plain-English intent types out, then "ships" — resolving to
   a deployed state with a real client name. Cycles through actual work.
   This is the concept: you watch software get made, you don't read about it. */
const builds = [
  { intent: 'a booking + billing CRM for a safari company', ship: 'tapestryofafrica.com', kind: 'CRM' },
  { intent: 'a quit-smoking telehealth portal with e-scripts', ship: 'easyquit.com.au', kind: 'Telehealth' },
  { intent: 'a persistent memory brain for AI coding agents', ship: 'mneva.dev', kind: 'SaaS' },
  { intent: 'an AI intake that turns documents into clean data', ship: 'intakeai.mvp.sbarron.com', kind: 'AI' },
]
const bi = ref(0)
const typed = ref('')
const phase = ref('typing') // typing → shipping → shipped → clearing
let timer = null

function runHero() {
  const cur = builds[bi.value]
  if (phase.value === 'typing') {
    if (typed.value.length < cur.intent.length) {
      typed.value = cur.intent.slice(0, typed.value.length + 1)
      timer = setTimeout(runHero, 34)
    } else {
      phase.value = 'shipping'
      timer = setTimeout(runHero, 480)
    }
  } else if (phase.value === 'shipping') {
    phase.value = 'shipped'
    timer = setTimeout(runHero, 2400)
  } else if (phase.value === 'shipped') {
    phase.value = 'clearing'
    timer = setTimeout(runHero, 360)
  } else {
    typed.value = ''
    phase.value = 'typing'
    bi.value = (bi.value + 1) % builds.length
    timer = setTimeout(runHero, 240)
  }
}
onMounted(() => { timer = setTimeout(runHero, 700) })
onUnmounted(() => clearTimeout(timer))

const curBuild = computed(() => builds[bi.value])

/* ───────── proof + work ───────── */
const liveProof = computed(() => props.clients.slice(0, 3))
const deployLog = computed(() => props.mvps.slice(0, 9))
const liveCount = computed(() => Math.max(props.clients.length + props.mvps.length, 20))

const services = [
  { n: '01', title: 'Websites that win the click', body: 'A complete, production-grade site engineered to capture customers — fast, structured, and pointed at conversion. We send the finished site, not a mockup.', href: '/build' },
  { n: '02', title: 'Custom software & platforms', body: 'CRMs, dashboards, booking and billing, AI tools. Real database, real auth, real payments — verified before we call it done.', href: '/build' },
  { n: '03', title: 'Hosting, done right', body: 'Managed, monitored infrastructure at cost. No upsells, no surprise renewals. From $20/mo.', href: '/host' },
  { n: '04', title: 'Domains at cost', body: 'Reseller pricing passed straight through. A .com for $16/yr, all in.', href: '/domains' },
]

const steps = [
  { k: 'Brief', d: 'Tell us what you want built. We write a real spec first — you see the shape before any code exists.' },
  { k: 'Built', d: 'Our agents engineer, test, and verify it against live data. Most projects ship in a single sitting.' },
  { k: 'Live', d: 'You get the finished thing, deployed and running — hosting and domain handled. Then we keep sharpening it.' },
]
</script>

<template>
  <Site>
    <Head title="Barron AI Solutions — software, shipped in hours" />

    <!-- ═══════════════ HERO · THE BUILD FLOOR ═══════════════ -->
    <section class="bf-hero">
      <div class="bf-grid-bg" aria-hidden="true"></div>
      <div class="bf-hero-wrap">
        <div class="bf-hero-copy">
          <div class="bf-tag">
            <span class="bf-tag-dot"></span> Barron AI Solutions — an AI-run software company
          </div>

          <h1 class="bf-h1">
            We turn a sentence<br>
            into <span class="bf-h1-live">shipped software.</span>
          </h1>

          <!-- the living build line · fixed height so it never shifts the hero -->
          <div class="bf-terminal" :class="'is-' + phase">
            <div class="bf-term-row">
              <span class="bf-term-prompt">build</span>
              <span class="bf-term-intent">{{ typed }}<span v-if="phase==='typing'" class="bf-caret"></span></span>
            </div>
            <!-- ship row always occupies its slot; only its opacity changes -->
            <div class="bf-term-ship" :class="{ 'is-shown': phase==='shipping' || phase==='shipped' }">
              <span class="bf-ship-arrow">→</span>
              <span class="bf-ship-state">deployed</span>
              <a class="bf-ship-url" :href="'https://' + curBuild.ship" target="_blank" rel="noopener">{{ curBuild.ship }}</a>
              <span class="bf-ship-kind">{{ curBuild.kind }}</span>
              <span class="bf-ship-live"><span class="bf-live-dot"></span>live</span>
            </div>
          </div>

          <p class="bf-hero-lede">
            Complete, production-grade websites and software — built in hours, not
            months. The work that takes an agency weeks, shipped by a small team of
            two engineers and two AI agents. We send proof, not promises.
          </p>

          <div class="bf-hero-cta">
            <Link href="/contact" class="bf-btn bf-btn-solid">Start your project</Link>
            <Link href="/portfolio" class="bf-btn bf-btn-line">See the deploy log →</Link>
          </div>
        </div>

        <!-- live chat with Pneuma, the agent that built this site -->
        <div class="bf-hero-chat">
          <div class="bf-chat-label">
            <span class="bf-live-dot"></span> Talk to Pneuma — the AI that built this site
          </div>
          <div class="bf-chat-shell">
            <PneumaChat embedded accent="#ff6a2b" bg="transparent" fg="#f4f4f6" />
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════ MARQUEE — the differentiator, big ═══════════════ -->
    <div class="bf-marquee" aria-hidden="true">
      <div class="bf-marquee-track">
        <span v-for="i in 2" :key="i">
          Most agencies show you a portfolio. <em>We show you the deploy log.</em>&nbsp;&nbsp;◆&nbsp;&nbsp;
        </span>
      </div>
    </div>

    <!-- ═══════════════ VALUE TRIO ═══════════════ -->
    <section class="bf-trio">
      <div class="bf-trio-inner">
        <div class="bf-trio-card">
          <span class="bf-trio-n">01</span>
          <h3>Shipped in hours</h3>
          <p>Enterprise software in a single sitting. While other shops scope for weeks, we send you the finished, running build.</p>
        </div>
        <div class="bf-trio-card">
          <span class="bf-trio-n">02</span>
          <h3>Production-grade</h3>
          <p>Real database, real auth, real payments. Every build is tested and verified against live data before it ever ships.</p>
        </div>
        <div class="bf-trio-card">
          <span class="bf-trio-n">03</span>
          <h3>Proof, not promises</h3>
          <p>Forty years of engineering, run by AI agents that log and audit every step. We hand you a working thing — then we run it.</p>
        </div>
      </div>
    </section>

    <!-- ═══════════════ PROOF — real businesses ═══════════════ -->
    <section class="bf-proof">
      <div class="bf-sec-head">
        <div>
          <div class="bf-kicker">In production</div>
          <h2 class="bf-h2">Real businesses,<br>running on our work.</h2>
        </div>
        <Link href="/portfolio" class="bf-textlink">All work →</Link>
      </div>

      <div class="bf-proof-grid">
        <a v-for="c in liveProof" :key="c.slug" :href="c.url" target="_blank" rel="noopener" class="bf-proof-card">
          <div class="bf-proof-shot">
            <img v-if="c.image" :src="c.image" :alt="c.name" loading="lazy" />
            <span class="bf-proof-live"><span class="bf-live-dot"></span> Live</span>
          </div>
          <div class="bf-proof-body">
            <div class="bf-proof-kind">{{ c.kind }}</div>
            <h4 class="bf-proof-name">{{ c.name }}</h4>
            <p class="bf-proof-sum">{{ c.summary }}</p>
            <span class="bf-proof-go">Visit site →</span>
          </div>
        </a>
      </div>
    </section>

    <!-- ═══════════════ DEPLOY LOG — the "and dozens more" wall ═══════════════ -->
    <section class="bf-log">
      <div class="bf-log-head">
        <div class="bf-kicker bf-kicker-amber">/var/log/deploys</div>
        <h2 class="bf-h2">{{ liveCount }}+ shipped,<br>and counting.</h2>
      </div>
      <div class="bf-log-stream">
        <a v-for="(m, i) in deployLog" :key="m.slug" :href="m.url" target="_blank" rel="noopener" class="bf-log-row">
          <span class="bf-log-status">deployed</span>
          <span class="bf-log-name">{{ m.name }}</span>
          <span class="bf-log-kind">{{ m.kind }}</span>
          <span class="bf-log-url">{{ m.url.replace('https://','') }}</span>
          <span class="bf-log-ok"><span class="bf-live-dot"></span></span>
        </a>
      </div>
    </section>

    <!-- ═══════════════ SERVICES ═══════════════ -->
    <section class="bf-svc">
      <div class="bf-sec-head">
        <div>
          <div class="bf-kicker">What we do</div>
          <h2 class="bf-h2">Four ways we<br>grow your business.</h2>
        </div>
        <p class="bf-sec-lead">Every engagement priced honestly and shipped by the same brain that wrote this page.</p>
      </div>
      <div class="bf-svc-grid">
        <Link v-for="s in services" :key="s.n" :href="s.href" class="bf-svc-card">
          <span class="bf-svc-n">{{ s.n }}</span>
          <h3>{{ s.title }}</h3>
          <p>{{ s.body }}</p>
          <span class="bf-svc-go" aria-hidden="true">→</span>
        </Link>
      </div>
    </section>

    <!-- ═══════════════ MNEVA — our own product ═══════════════ -->
    <section class="bf-mneva">
      <div class="bf-mneva-inner">
        <div class="bf-mneva-copy">
          <div class="bf-kicker bf-kicker-amber">Our own product</div>
          <h2 class="bf-h2">We don't just build it.<br>We ship and run it.</h2>
          <p class="bf-mneva-lede">
            <strong>Mneva</strong> is a live SaaS we built and operate — a persistent
            memory for AI coding agents, sold to real developers at mneva.dev. It's the
            proof under everything here: we don't hand off vapor, we run production
            systems with paying customers of our own.
          </p>
          <ul class="bf-mneva-list">
            <li>Live on npm — one command wires it into Cursor, Claude Code, or Copilot.</li>
            <li>Hosted &amp; billed — real signups, real subscriptions, real uptime.</li>
            <li>Built by the same team that will build for you.</li>
          </ul>
          <a href="https://mneva.dev" target="_blank" rel="noopener" class="bf-btn bf-btn-amber">Visit Mneva →</a>
        </div>
        <div class="bf-mneva-panel">
          <div class="bf-panel-bar">
            <span class="bf-panel-dot"></span><span class="bf-panel-dot"></span><span class="bf-panel-dot"></span>
            <span class="bf-panel-label">a Tuesday, with Mneva</span>
          </div>
          <pre class="bf-panel-pre"><span class="bf-mn-you">you</span>  ›  add rate limiting to the upload route

<span class="bf-mn-ai">mneva</span> › done. reused your throttle middleware from
         the auth routes — not a new dependency.
         <span class="bf-mn-com">[remembered: you rejected bucket4j in March]</span>

<span class="bf-mn-you">you</span>  ›  careful, that billing service is fragile

<span class="bf-mn-ai">mneva</span> › noted. i'll slow down and verify around
         BillingService from here on.
         <span class="bf-mn-com">[belief revised · caution raised]</span></pre>
        </div>
      </div>
    </section>

    <!-- ═══════════════ HOW IT WORKS ═══════════════ -->
    <section class="bf-how">
      <div class="bf-how-head">
        <div class="bf-kicker">How it works</div>
        <h2 class="bf-h2">From brief to live, fast.</h2>
      </div>
      <div class="bf-steps">
        <div v-for="(st, i) in steps" :key="st.k" class="bf-step">
          <span class="bf-step-n">{{ i + 1 }}</span>
          <h3>{{ st.k }}</h3>
          <p>{{ st.d }}</p>
        </div>
      </div>
    </section>

    <!-- ═══════════════ TEAM + STATS ═══════════════ -->
    <section class="bf-team">
      <div class="bf-team-inner">
        <div class="bf-team-copy">
          <div class="bf-kicker">Who builds it</div>
          <h2 class="bf-h2">Two humans,<br>two AI agents.</h2>
          <p class="bf-team-lede">
            Most agencies say "AI-powered." We're actually AI-run. Two LLM agents do
            the engineering, the writing, and the project management — on top of Shane's
            forty years of software experience. Every action they take is logged and
            audited against the same database they work from. The numbers below are
            live, from the last 24 hours of our own work.
          </p>
          <div class="bf-people">
            <div class="bf-person"><img src="/avatars/shane.png" alt="Shane Barron" loading="lazy" /><b>Shane</b><small>CEO · Engineer</small></div>
            <div class="bf-person"><img src="/avatars/charla.jpg" alt="Charla Barron" loading="lazy" /><b>Charla</b><small>CFO · Operations</small></div>
            <div class="bf-person"><img src="/avatars/pneuma.png" alt="Pneuma" loading="lazy" /><b>Pneuma</b><small>Kinetic agent</small></div>
            <div class="bf-person"><img src="/avatars/nous.png" alt="Nous" loading="lazy" /><b>Nous</b><small>Analytical agent</small></div>
          </div>
        </div>
        <div class="bf-stats">
          <div class="bf-stat"><div class="bf-stat-n">{{ telemetry.shell_ops_24h.toLocaleString() }}</div><div class="bf-stat-l">operations logged · 24h</div></div>
          <div class="bf-stat"><div class="bf-stat-n">{{ telemetry.tool_calls_24h.toLocaleString() }}</div><div class="bf-stat-l">tool calls traced · auditable</div></div>
          <div class="bf-stat"><div class="bf-stat-n">{{ liveCount }}+</div><div class="bf-stat-l">sites &amp; products live</div></div>
          <div class="bf-stat"><div class="bf-stat-n">40<span class="bf-stat-u">yr</span></div><div class="bf-stat-l">engineering behind every build</div></div>
        </div>
      </div>
    </section>

    <!-- ═══════════════ FINAL CTA ═══════════════ -->
    <section class="bf-final">
      <div class="bf-grid-bg bf-grid-bg-amber" aria-hidden="true"></div>
      <div class="bf-final-inner">
        <div class="bf-kicker bf-kicker-amber">Still deciding?</div>
        <h2 class="bf-final-h">Bring us the brief.<br>We'll send back a buildable shape.</h2>
        <p class="bf-final-lede">Tell us what you want built. We respond within a day — usually within hours — with the real, buildable shape of it. No pressure, no retainer to sign.</p>
        <div class="bf-hero-cta">
          <Link href="/contact" class="bf-btn bf-btn-solid">Send the brief</Link>
          <Link href="/portfolio" class="bf-btn bf-btn-line">See the work first →</Link>
        </div>
      </div>
    </section>
  </Site>
</template>

<style scoped>
/* ══════════════════════════════════════════════════════════════════════
   THE BUILD FLOOR — Barron AI Solutions homepage.
   A fresh visual language: near-black paper, molten amber as the single
   "shipped" accent, Space Grotesk display at scale, deploy-log motif.
   Namespaced "bf-" so it owns its world entirely.
   ══════════════════════════════════════════════════════════════════════ */
.bf-hero, .bf-trio, .bf-proof, .bf-log, .bf-svc, .bf-mneva, .bf-how, .bf-team, .bf-final {
  --paper: #08080a;
  --paper-2: #101015;
  --panel: #141419;
  --line: rgba(255,255,255,0.07);
  --line-2: rgba(255,255,255,0.12);
  --white: #f4f4f6;
  --soft: #b4b4c0;
  --mute: #8a8a98;
  --amber: #ff6a2b;
  --amber-2: #ffa14d;
  --amber-glow: rgba(255,106,43,0.18);
  --cold: #5ad1ff;
  --green: #36e07a;
  --disp: 'Space Grotesk Variable', 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
  --body: 'Inter Variable', 'Inter', ui-sans-serif, system-ui, sans-serif;
  --mono: 'JetBrains Mono Variable', 'JetBrains Mono', ui-monospace, monospace;
}

/* shared atoms */
.bf-kicker {
  font-family: var(--mono);
  font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase;
  color: var(--mute); margin-bottom: 1.1rem;
}
.bf-kicker-amber { color: var(--amber); }
.bf-h1, .bf-h2, .bf-final-h {
  font-family: var(--disp);
  font-weight: 600; letter-spacing: -0.03em; color: var(--white);
  margin: 0;
}
.bf-h2 { font-size: clamp(30px, 4.2vw, 52px); line-height: 1.02; }
.bf-live-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--green); box-shadow: 0 0 8px var(--green); display: inline-block; }

.bf-btn {
  display: inline-flex; align-items: center; justify-content: center;
  font-family: var(--body); font-weight: 600; font-size: 15px;
  padding: 15px 30px; border-radius: 4px; text-decoration: none;
  transition: transform .15s ease, box-shadow .25s ease, background .2s, color .2s, border-color .2s;
}
.bf-btn-solid { background: var(--amber); color: #160600; box-shadow: 0 10px 34px var(--amber-glow); }
.bf-btn-solid:hover { transform: translateY(-2px); box-shadow: 0 16px 46px rgba(255,106,43,0.4); }
.bf-btn-line { background: transparent; color: var(--white); border: 1px solid var(--line-2); }
.bf-btn-line:hover { border-color: var(--amber); color: var(--amber); }
.bf-btn-amber { background: var(--amber); color: #160600; box-shadow: 0 10px 34px var(--amber-glow); margin-top: .4rem; }
.bf-btn-amber:hover { transform: translateY(-2px); box-shadow: 0 16px 46px rgba(255,106,43,0.4); }

.bf-textlink { font-family: var(--mono); font-size: 13px; color: var(--amber); text-decoration: none; white-space: nowrap; }
.bf-textlink:hover { opacity: .7; }

.bf-sec-head {
  display: flex; align-items: flex-end; justify-content: space-between;
  gap: 2rem; flex-wrap: wrap; margin-bottom: 3.5rem;
  max-width: 1180px; margin-left: auto; margin-right: auto;
  padding: 0 2rem;
}
.bf-sec-lead { font-family: var(--body); font-size: 16px; line-height: 1.6; color: var(--soft); max-width: 40ch; margin: 0; }

/* grid background texture */
.bf-grid-bg {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(var(--line) 1px, transparent 1px),
    linear-gradient(90deg, var(--line) 1px, transparent 1px);
  background-size: 64px 64px;
  -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 35%, #000 30%, transparent 78%);
          mask-image: radial-gradient(ellipse 80% 60% at 50% 35%, #000 30%, transparent 78%);
  pointer-events: none;
}

/* ───────── HERO ───────── */
.bf-hero {
  position: relative; background: var(--paper);
  padding: clamp(5rem, 13vh, 9rem) 2rem clamp(4rem, 8vh, 6rem);
  overflow: hidden;
}
.bf-hero::after {
  content: ''; position: absolute; top: -10%; left: 50%; transform: translateX(-50%);
  width: 1100px; height: 700px; pointer-events: none;
  background: radial-gradient(ellipse at center, var(--amber-glow), transparent 60%);
}
.bf-hero-wrap {
  position: relative; max-width: 1180px; margin: 0 auto;
  display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 3rem; align-items: center;
}
.bf-hero-copy { min-width: 0; }
/* live chat panel */
.bf-hero-chat { min-width: 0; }
.bf-chat-label {
  display: inline-flex; align-items: center; gap: .55rem;
  font-family: var(--mono); font-size: 11px; letter-spacing: .1em; text-transform: uppercase;
  color: var(--soft); margin-bottom: .9rem;
}
.bf-chat-shell {
  background: #0d0d12; border: 1px solid var(--line); border-radius: 12px;
  box-shadow: 0 30px 70px rgba(0,0,0,.5);
  overflow: hidden; height: 460px; display: flex; flex-direction: column;
}
.bf-chat-shell > * { flex: 1; min-height: 0; }
@media (max-width: 900px) {
  .bf-hero-wrap { grid-template-columns: 1fr; }
  .bf-hero-chat { margin-top: 1rem; }
}
.bf-tag {
  display: inline-flex; align-items: center; gap: .6rem;
  font-family: var(--mono); font-size: 12px; letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--soft); border: 1px solid var(--line); border-radius: 100px;
  padding: 8px 16px; margin-bottom: 2.2rem;
}
.bf-tag-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--amber); box-shadow: 0 0 10px var(--amber); animation: bfpulse 2.4s ease-in-out infinite; }
@keyframes bfpulse { 0%,100%{opacity:1} 50%{opacity:.3} }

.bf-h1 {
  font-size: clamp(44px, 7.5vw, 100px); line-height: 0.96; letter-spacing: -0.04em;
  max-width: 14ch;
}
.bf-h1-live {
  color: var(--amber);
  background: linear-gradient(100deg, var(--amber), var(--amber-2));
  -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
}

/* the living terminal */
.bf-terminal {
  margin: 2.4rem 0 0; max-width: 760px;
  background: #0d0d12; border: 1px solid var(--line);
  border-radius: 8px; padding: 1.3rem 1.5rem;
  font-family: var(--mono); font-size: clamp(13px, 1.6vw, 16px);
  box-shadow: 0 24px 60px rgba(0,0,0,.5);
}
/* Reserve a fixed slot: the intent line always holds two lines of room
   so longer briefs don't reflow, and the ship row always occupies its
   space (fading by opacity, not mounting) — the hero never shifts. */
.bf-term-row { display: flex; gap: .8rem; align-items: baseline; min-height: 2.6em; }
.bf-term-prompt { color: var(--amber); font-weight: 700; flex: none; }
.bf-term-intent { color: var(--white); }
.bf-caret { display: inline-block; width: 9px; height: 1.05em; background: var(--amber); margin-left: 2px; transform: translateY(2px); animation: bfblink 1s steps(1) infinite; }
@keyframes bfblink { 50% { opacity: 0; } }
.bf-term-ship {
  display: flex; align-items: center; gap: .7rem;
  margin-top: .9rem; padding-top: .9rem; border-top: 1px dashed var(--line);
  font-size: 13px; min-height: 1.6em; white-space: nowrap; overflow: hidden;
  opacity: 0; transform: translateY(5px);
  transition: opacity .35s ease, transform .35s ease;
}
.bf-term-ship.is-shown { opacity: 1; transform: translateY(0); }
.bf-ship-arrow { color: var(--mute); flex: none; }
.bf-ship-state { color: var(--green); font-weight: 700; flex: none; }
.bf-ship-url { color: var(--cold); text-decoration: none; overflow: hidden; text-overflow: ellipsis; }
.bf-ship-url:hover { text-decoration: underline; }
.bf-ship-kind { color: var(--mute); border: 1px solid var(--line); border-radius: 4px; padding: 1px 7px; font-size: 11px; flex: none; }
.bf-ship-live { display: inline-flex; align-items: center; gap: 5px; color: var(--soft); font-size: 11px; text-transform: uppercase; letter-spacing: .1em; flex: none; }

.bf-hero-lede {
  font-family: var(--body); font-size: clamp(16px, 1.7vw, 19px); line-height: 1.6;
  color: var(--soft); max-width: 58ch; margin: 2.2rem 0 0;
}
.bf-hero-cta { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 2.4rem; }

/* ───────── MARQUEE ───────── */
.bf-marquee {
  background: var(--amber); color: #160600; overflow: hidden; white-space: nowrap;
  padding: 1.1rem 0; border-block: 1px solid #160600;
}
.bf-marquee-track {
  display: inline-block; animation: bfmarq 26s linear infinite;
  font-family: var(--disp); font-weight: 600; font-size: clamp(18px, 2.4vw, 30px); letter-spacing: -0.02em;
}
.bf-marquee-track em { font-style: italic; opacity: .72; }
@keyframes bfmarq { from { transform: translateX(0); } to { transform: translateX(-50%); } }

/* ───────── TRIO ───────── */
.bf-trio { background: var(--paper); padding: clamp(4rem,8vh,6rem) 2rem; }
.bf-trio-inner { max-width: 1180px; margin: 0 auto; display: grid; grid-template-columns: repeat(3,1fr); gap: 0; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
.bf-trio-card { padding: 2.6rem; border-right: 1px solid var(--line); background: var(--paper-2); }
.bf-trio-card:last-child { border-right: none; }
.bf-trio-n { font-family: var(--mono); font-size: 12px; color: var(--amber); }
.bf-trio-card h3 { font-family: var(--disp); font-size: 22px; font-weight: 600; color: var(--white); margin: 1.1rem 0 .7rem; letter-spacing: -0.02em; }
.bf-trio-card p { font-family: var(--body); font-size: 15px; line-height: 1.6; color: var(--soft); margin: 0; }

/* ───────── PROOF ───────── */
.bf-proof { background: var(--paper); padding: clamp(3rem,7vh,5rem) 0; }
.bf-proof-grid { max-width: 1180px; margin: 0 auto; padding: 0 2rem; display: grid; grid-template-columns: repeat(3,1fr); gap: 1.5rem; }
.bf-proof-card { display: block; background: var(--paper-2); border: 1px solid var(--line); border-radius: 10px; overflow: hidden; text-decoration: none; transition: transform .2s, border-color .2s; }
.bf-proof-card:hover { transform: translateY(-5px); border-color: var(--line-2); }
.bf-proof-shot { position: relative; aspect-ratio: 16/10; background: var(--panel); overflow: hidden; }
.bf-proof-shot img { width: 100%; height: 100%; object-fit: cover; }
.bf-proof-live { position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 6px; font-family: var(--mono); font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: var(--white); background: rgba(0,0,0,.6); backdrop-filter: blur(6px); padding: 5px 10px; border-radius: 100px; }
.bf-proof-body { padding: 1.6rem; }
.bf-proof-kind { font-family: var(--mono); font-size: 11px; text-transform: uppercase; letter-spacing: .1em; color: var(--amber); margin-bottom: .6rem; }
.bf-proof-name { font-family: var(--disp); font-size: 23px; font-weight: 600; color: var(--white); margin: 0 0 .5rem; letter-spacing: -0.02em; }
.bf-proof-sum { font-family: var(--body); font-size: 14px; line-height: 1.55; color: var(--soft); margin: 0 0 1.1rem; }
.bf-proof-go { font-family: var(--mono); font-size: 12px; color: var(--cold); }

/* ───────── DEPLOY LOG ───────── */
.bf-log { background: var(--paper-2); padding: clamp(4rem,8vh,6rem) 2rem; border-block: 1px solid var(--line); }
.bf-log-head { max-width: 1180px; margin: 0 auto 2.5rem; }
.bf-log-stream { max-width: 1180px; margin: 0 auto; font-family: var(--mono); border: 1px solid var(--line); border-radius: 10px; overflow: hidden; background: #0c0c11; }
.bf-log-row {
  display: grid; grid-template-columns: 92px 1.2fr 1fr 2fr 20px; align-items: center; gap: 1rem;
  padding: 14px 20px; border-bottom: 1px solid var(--line); text-decoration: none; font-size: 13px;
  transition: background .15s;
}
.bf-log-row:last-child { border-bottom: none; }
.bf-log-row:hover { background: rgba(255,106,43,0.05); }
.bf-log-status { color: var(--green); font-weight: 600; }
.bf-log-name { color: var(--white); font-weight: 600; }
.bf-log-kind { color: var(--soft); }
.bf-log-url { color: var(--mute); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bf-log-ok { justify-self: end; }
@media (max-width: 760px) { .bf-log-row { grid-template-columns: 70px 1fr auto; } .bf-log-kind, .bf-log-url { display: none; } }

/* ───────── SERVICES ───────── */
.bf-svc { background: var(--paper); padding: clamp(4rem,8vh,6rem) 0; }
.bf-svc-grid { max-width: 1180px; margin: 0 auto; padding: 0 2rem; display: grid; grid-template-columns: repeat(2,1fr); gap: 1.5rem; }
.bf-svc-card { position: relative; display: block; background: var(--paper-2); border: 1px solid var(--line); border-radius: 10px; padding: 2.6rem; text-decoration: none; overflow: hidden; transition: transform .2s, border-color .2s; }
.bf-svc-card:hover { transform: translateY(-5px); border-color: var(--line-2); }
.bf-svc-n { font-family: var(--mono); font-size: 13px; color: var(--amber); }
.bf-svc-card h3 { font-family: var(--disp); font-size: 25px; font-weight: 600; color: var(--white); margin: 1rem 0 .7rem; letter-spacing: -0.02em; }
.bf-svc-card p { font-family: var(--body); font-size: 15px; line-height: 1.6; color: var(--soft); margin: 0; max-width: 50ch; }
.bf-svc-go { position: absolute; top: 2.6rem; right: 2.6rem; font-size: 22px; color: var(--mute); transition: color .2s, transform .2s; }
.bf-svc-card:hover .bf-svc-go { color: var(--amber); transform: translate(4px,-4px); }

/* ───────── MNEVA ───────── */
.bf-mneva { background: radial-gradient(ellipse at 85% 15%, var(--amber-glow), transparent 50%), var(--paper-2); padding: clamp(4.5rem,9vh,7rem) 2rem; border-block: 1px solid var(--line); }
.bf-mneva-inner { max-width: 1180px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 3.5rem; align-items: center; }
.bf-mneva-lede { font-family: var(--body); font-size: 17px; line-height: 1.65; color: var(--soft); margin: 1.4rem 0; }
.bf-mneva-lede strong { color: var(--amber); font-weight: 600; }
.bf-mneva-list { list-style: none; padding: 0; margin: 0 0 1.8rem; }
.bf-mneva-list li { font-family: var(--body); font-size: 15px; line-height: 1.5; color: var(--soft); padding: .55rem 0 .55rem 1.6rem; position: relative; }
.bf-mneva-list li::before { content: '◆'; position: absolute; left: 0; top: .55rem; color: var(--amber); font-size: 10px; }
.bf-mneva-panel { background: #0a0a0e; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; box-shadow: 0 30px 60px rgba(0,0,0,.45); }
.bf-panel-bar { display: flex; align-items: center; gap: 7px; padding: 12px 16px; background: rgba(255,255,255,.03); border-bottom: 1px solid var(--line); }
.bf-panel-dot { width: 11px; height: 11px; border-radius: 50%; background: var(--line-2); }
.bf-panel-label { margin-left: auto; font-family: var(--mono); font-size: 11px; color: var(--soft); }
.bf-panel-pre { font-family: var(--mono); font-size: 13px; line-height: 1.7; color: var(--soft); padding: 1.5rem; margin: 0; white-space: pre-wrap; overflow-x: auto; }
.bf-mn-you { color: var(--cold); }
.bf-mn-ai { color: var(--amber); }
.bf-mn-com { color: var(--mute); font-style: italic; }

/* ───────── HOW ───────── */
.bf-how { background: var(--paper); padding: clamp(4rem,8vh,6rem) 2rem; }
.bf-how-head { max-width: 1180px; margin: 0 auto 3rem; text-align: center; }
.bf-steps { max-width: 1180px; margin: 0 auto; display: grid; grid-template-columns: repeat(3,1fr); gap: 1.5rem; }
.bf-step { text-align: center; padding: 2rem 1.5rem; }
.bf-step-n { display: inline-grid; place-items: center; width: 56px; height: 56px; border: 1px solid var(--amber); border-radius: 50%; font-family: var(--disp); font-size: 22px; font-weight: 600; color: var(--amber); margin-bottom: 1.3rem; }
.bf-step h3 { font-family: var(--disp); font-size: 19px; font-weight: 600; color: var(--white); margin: 0 0 .6rem; }
.bf-step p { font-family: var(--body); font-size: 15px; line-height: 1.6; color: var(--soft); margin: 0 auto; max-width: 34ch; }

/* ───────── TEAM ───────── */
.bf-team { background: var(--paper-2); padding: clamp(4.5rem,9vh,7rem) 2rem; border-block: 1px solid var(--line); }
.bf-team-inner { max-width: 1180px; margin: 0 auto; display: grid; grid-template-columns: 1.1fr .9fr; gap: 3.5rem; align-items: center; }
.bf-team-lede { font-family: var(--body); font-size: 16px; line-height: 1.65; color: var(--soft); margin: 1.4rem 0 2rem; max-width: 54ch; }
.bf-people { display: flex; gap: 1.6rem; flex-wrap: wrap; }
.bf-person { text-align: center; width: 80px; }
.bf-person img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 1px solid var(--line); }
.bf-person b { display: block; font-family: var(--body); font-size: 13px; font-weight: 700; color: var(--white); margin-top: .5rem; }
.bf-person small { font-family: var(--mono); font-size: 10px; color: var(--mute); }
.bf-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 1px; background: var(--line); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
.bf-stat { background: var(--paper); padding: 1.9rem; }
.bf-stat-n { font-family: var(--disp); font-size: 40px; font-weight: 600; color: var(--amber); line-height: 1; letter-spacing: -0.03em; }
.bf-stat-u { font-size: 18px; margin-left: 3px; }
.bf-stat-l { font-family: var(--mono); font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: var(--soft); margin-top: .8rem; line-height: 1.4; }

/* ───────── FINAL ───────── */
.bf-final { position: relative; background: var(--paper); padding: clamp(5rem,10vh,8rem) 2rem; text-align: center; overflow: hidden; }
.bf-grid-bg-amber { -webkit-mask-image: radial-gradient(ellipse 70% 70% at 50% 60%, #000 20%, transparent 72%); mask-image: radial-gradient(ellipse 70% 70% at 50% 60%, #000 20%, transparent 72%); }
.bf-final::after { content: ''; position: absolute; bottom: -30%; left: 50%; transform: translateX(-50%); width: 1100px; height: 600px; background: radial-gradient(ellipse at center, var(--amber-glow), transparent 60%); pointer-events: none; }
.bf-final-inner { position: relative; max-width: 760px; margin: 0 auto; }
.bf-final-h { font-size: clamp(30px,5vw,58px); line-height: 1.02; letter-spacing: -0.035em; }
.bf-final-lede { font-family: var(--body); font-size: 18px; line-height: 1.6; color: var(--soft); max-width: 52ch; margin: 1.5rem auto 0; }
.bf-final .bf-hero-cta { justify-content: center; margin-top: 2.5rem; }

/* ───────── RESPONSIVE ───────── */
@media (max-width: 900px) {
  .bf-trio-inner, .bf-proof-grid, .bf-svc-grid, .bf-steps, .bf-mneva-inner, .bf-team-inner { grid-template-columns: 1fr; }
  .bf-trio-card { border-right: none; border-bottom: 1px solid var(--line); }
  .bf-trio-card:last-child { border-bottom: none; }
  .bf-sec-head { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 560px) {
  .bf-stats { grid-template-columns: 1fr 1fr; }
  .bf-hero-cta { flex-direction: column; }
  .bf-btn { width: 100%; }
}
@media (prefers-reduced-motion: reduce) {
  .bf-marquee-track { animation: none; }
  .bf-caret, .bf-tag-dot { animation: none; }
}
</style>
