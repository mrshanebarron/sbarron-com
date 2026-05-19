<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Site from '@/Layouts/Site.vue'

defineProps({
  process: { type: Array, default: () => [] },
})
</script>

<template>
  <Site>
    <Head title="Build — Barron AI Solutions" />

    <section class="section">
      <div class="container-wide">
        <div class="section-label">How we build</div>
        <h1 class="display" style="margin-bottom: 1.5rem;">
          Software, <span class="mark">shipped</span>.
        </h1>
        <p class="lede" style="max-width: 60ch;">
          We treat every brief as buildable. The agents read the requirements,
          write a spec, commit it, then build against it. The substrate audits the work.
          Nobody says done without proof.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div class="section-label">The pipeline · six phases</div>

        <ol style="list-style: none; padding: 0; margin: 0; border-top: 1px solid var(--ink);">
          <li
            v-for="(phase, i) in process"
            :key="phase.phase"
            style="display: grid; grid-template-columns: 9rem 1fr; gap: clamp(1.5rem, 4vw, 4rem); padding-block: 2.5rem; border-bottom: 1px solid var(--rule-soft);"
          >
            <div>
              <div class="display-md" style="font-size: clamp(2.5rem, 4vw, 4rem); color: var(--oxblood); line-height: 1;">
                {{ String(i + 1).padStart(2, '0') }}
              </div>
              <div class="micro" style="margin-top: 0.75rem;">{{ phase.duration }}</div>
            </div>
            <div>
              <h2 class="display-sm" style="margin-bottom: 0.85rem;">{{ phase.phase }}</h2>
              <p class="prose-body" style="max-width: 56ch;">{{ phase.description }}</p>
            </div>
          </li>
        </ol>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
          <div>
            <div class="section-label">Stack we default to</div>
            <ul style="list-style: none; padding: 0; margin: 0; font-family: var(--font-mono); font-size: 13px; line-height: 1.8; color: var(--bone-deep);">
              <li>— Laravel 13 · Inertia · Vue 3 · Tailwind 4</li>
              <li>— Next.js 15 + React 19 when the UX wants it</li>
              <li>— PostgreSQL primary · Redis cache + queue</li>
              <li>— nginx + Let's Encrypt · DigitalOcean</li>
              <li>— Playwright E2E · Pest / Vitest unit</li>
              <li>— Git-push deploys with post-receive hooks</li>
            </ul>
          </div>
          <div>
            <div class="section-label">What we don't do</div>
            <ul style="list-style: none; padding: 0; margin: 0; font-family: var(--font-mono); font-size: 13px; line-height: 1.8; color: var(--bone-deep);">
              <li>— Lorem ipsum shipped to production</li>
              <li>— Stock photo placeholders on a "demo"</li>
              <li>— "Should work" — we run the flow first</li>
              <li>— Mocked tests pretending to be integration tests</li>
              <li>— Skipping the migration you'd rather not run</li>
              <li>— Calling anything done without an end-to-end check</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section class="section-last">
      <div class="container-wide">
        <h2 class="display-md" style="margin-bottom: 1rem;">Have a brief?</h2>
        <p class="lede" style="max-width: 52ch; margin-bottom: 2rem;">
          Send it. We will read it end to end, tell you the buildable shape, and quote the work.
        </p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <Link href="/contact" class="btn btn-primary">Start a project →</Link>
          <Link href="/host" class="btn btn-secondary">See hosting</Link>
        </div>
      </div>
    </section>
  </Site>
</template>
