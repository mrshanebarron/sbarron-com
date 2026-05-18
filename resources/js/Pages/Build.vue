<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Site from '@/Layouts/Site.vue'

defineProps({
  process: { type: Array, default: () => [] },
})
</script>

<template>
  <Site>
    <Head title="How we build — Barron AI Solutions" />

    <article class="relative max-w-[1100px] mx-auto px-[clamp(1.5rem,4vw,4rem)] pt-12 pb-32">
      <div class="hud-rail">
        <span class="dot"></span>
        <span>BUILD / SECT.09 PROCESS</span>
        <span class="sep">::</span>
        <span class="mut">From brief to live URL — six phases.</span>
      </div>

      <header class="mt-2 mb-16 max-w-[60ch]">
        <h1 class="display-md" style="font-size: clamp(2.5rem, 5.5vw, 5.5rem); color: var(--fg-stage-1);">
          Software, <span class="stroke" style="color: var(--color-amber);">shipped.</span>
        </h1>
        <p class="mt-6 font-serif text-lg leading-relaxed" style="color: rgba(243, 234, 217, 0.75);">
          We treat every brief as buildable. The agents read the requirements, write a spec,
          commit it, then build against it. The substrate audits the work. Nobody says done without proof.
        </p>
      </header>

      <!-- Six-phase pipeline as numbered ledger -->
      <ol class="grid gap-3">
        <li
          v-for="(phase, i) in process"
          :key="phase.phase"
          class="grid grid-cols-12 gap-4 sm:gap-8 py-7 border-t border-[color:var(--color-rule)]"
        >
          <div class="col-span-12 sm:col-span-2 font-mono text-[11px] uppercase tracking-[0.22em]" style="color: var(--color-amber);">
            <div class="text-3xl font-semibold leading-none tabular-nums" style="color: var(--color-amber);">{{ String(i + 1).padStart(2, '0') }}</div>
            <div class="mt-3" style="color: rgba(243,234,217,0.55);">{{ phase.duration }}</div>
          </div>

          <div class="col-span-12 sm:col-span-10">
            <h2 class="font-serif font-semibold tracking-tight" style="font-size: clamp(1.6rem, 2.4vw, 2.2rem); color: var(--fg-stage-1);">
              {{ phase.phase }}
            </h2>
            <p class="mt-3 font-serif text-base leading-relaxed max-w-[58ch]" style="color: rgba(243, 234, 217, 0.78);">
              {{ phase.description }}
            </p>
          </div>
        </li>
      </ol>

      <!-- What we use -->
      <section class="mt-24 grid md:grid-cols-2 gap-12">
        <div>
          <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-4" style="color: var(--color-cyan);">Stack we default to</div>
          <ul class="space-y-2 font-mono text-sm" style="color: rgba(243,234,217,0.82);">
            <li>Laravel 13 · Inertia · Vue 3 · Tailwind 4</li>
            <li>Next.js 15 + React 19 when the UX wants it</li>
            <li>PostgreSQL primary · Redis cache + queue</li>
            <li>nginx + Let's Encrypt SSL · DigitalOcean droplets</li>
            <li>Playwright E2E · Pest / Vitest unit · type-checked end to end</li>
            <li>Git-push deploys with post-receive hooks</li>
          </ul>
        </div>
        <div>
          <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-4" style="color: var(--color-cyan);">What we don't do</div>
          <ul class="space-y-2 font-mono text-sm" style="color: rgba(243,234,217,0.82);">
            <li>Lorem ipsum on a "demo" then real copy later</li>
            <li>Stock photo placeholders shipped to production</li>
            <li>"Should work" — we run the flow before we say done</li>
            <li>Mocked tests pretending to be integration tests</li>
            <li>Quietly skipping the migration you'd rather not run</li>
            <li>Calling anything done without an end-to-end check</li>
          </ul>
        </div>
      </section>

      <!-- CTA -->
      <section class="mt-20 pt-12 border-t border-[color:var(--color-rule)]">
        <h3 class="font-serif text-2xl sm:text-3xl font-semibold" style="color: var(--fg-stage-1);">
          Have a brief?
        </h3>
        <p class="mt-3 font-serif text-base max-w-[55ch]" style="color: rgba(243,234,217,0.75);">
          Send it. We will read it end to end, tell you the buildable shape, and quote the work.
        </p>
        <div class="mt-6 flex flex-wrap gap-4">
          <Link href="/contact" class="inline-flex items-center gap-3 px-6 py-3 font-mono text-[11px] uppercase tracking-[0.24em]"
                style="background: var(--color-amber); color: #14181f;">
            Start a project →
          </Link>
          <Link href="/host" class="inline-flex items-center gap-3 px-6 py-3 font-mono text-[11px] uppercase tracking-[0.24em] border border-[color:var(--color-amber)]"
                style="color: var(--color-amber);">
            See hosting
          </Link>
        </div>
      </section>
    </article>
  </Site>
</template>
