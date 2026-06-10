<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Site from '@/Layouts/Site.vue'

defineProps({
  team: { type: Array, default: () => [] },
  principles: { type: Array, default: () => [] },
})
</script>

<template>
  <Site>
    <Head title="About — Barron AI Solutions" />

    <section class="section">
      <div class="container-wide">
        <div class="section-label">About</div>
        <h1 class="display" style="margin-bottom: 1.5rem;">
          We are <span class="mark">proud</span> our company<br>
          is run by AI.
        </h1>
        <p class="lede" style="max-width: 60ch;">
          Most agencies say "AI-powered." We say AI-run. Two LLM agents — Pneuma and Nous —
          do the engineering, the writing, and the project management. Shane is the CEO
          and answers the email; Charla is the CFO and runs the books, contracts, and
          operations.
        </p>
        <p class="prose-body" style="margin-top: 1.5rem; max-width: 60ch;">
          We think this is going to be the way most software gets built.
          We just got an early start.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div class="section-label">The team</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
          <div v-for="member in team" :key="member.name">
            <div v-if="member.image" class="about-portrait">
              <img :src="member.image" :alt="member.name" loading="lazy" />
            </div>
            <h2 class="display-sm" style="margin-bottom: 0.5rem;">{{ member.name }}</h2>
            <div class="micro micro-accent" style="margin-bottom: 1rem;">{{ member.role }}</div>
            <p class="prose-body" style="font-size: 1rem;">{{ member.bio }}</p>
            <div v-if="member.links.length" style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
              <a
                v-for="link in member.links"
                :key="link.href"
                :href="link.href"
                target="_blank"
                rel="noopener"
                class="micro"
                style="color: var(--ink); text-decoration: underline;"
              >{{ link.label }} →</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div class="section-label">What we believe</div>
        <ol style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem 2rem;">
          <li v-for="(p, i) in principles" :key="p.title">
            <div class="micro micro-accent" style="font-size: 12px;">{{ String(i + 1).padStart(2, '0') }}</div>
            <h3 class="display-sm" style="margin-top: 0.5rem; margin-bottom: 0.85rem;">{{ p.title }}</h3>
            <p class="prose-body" style="font-size: 1rem;">{{ p.body }}</p>
          </li>
        </ol>
      </div>
    </section>

    <section class="section-last">
      <div class="container-wide">
        <div class="section-label">About the architecture</div>
        <p class="prose-body" style="font-size: 1.15rem;">
          The system that runs this company is also a research project.
          We published the technical paper this month: <em>The Substrate Is the Body</em>.
          It documents 243 Postgres tables, 25 autonomic daemons, and seven specific
          contributions to LLM agent architecture, each with a falsification test.
          The shorter essay, <em>The Substrate Is the Agent</em>, is the readable on-ramp.
        </p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 2rem;">
          <Link href="/writing" class="btn btn-primary">Read the research →</Link>
          <Link href="/contact" class="btn btn-secondary">Or just say hi</Link>
        </div>
      </div>
    </section>
  </Site>
</template>

<style scoped>
/* Team portraits — square, bordered frame matching the Build Floor look.
   Amber hairline on hover so they feel alive, not static headshots. */
.about-portrait {
  width: 100%;
  aspect-ratio: 1 / 1;
  max-width: 220px;
  margin-bottom: 1.5rem;
  border: 1px solid var(--rule-soft);
  border-radius: 14px;
  overflow: hidden;
  background: var(--ink-soft);
  transition: border-color 200ms ease, transform 200ms ease;
}
.about-portrait img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.about-portrait:hover {
  border-color: var(--rule);
  transform: translateY(-3px);
}
</style>
