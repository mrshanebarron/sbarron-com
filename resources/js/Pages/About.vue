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

    <article class="relative max-w-[1100px] mx-auto px-[clamp(1.5rem,4vw,4rem)] pt-12 pb-32">
      <div class="hud-rail">
        <span class="dot"></span>
        <span>ABOUT / SECT.09 PERSONNEL</span>
        <span class="sep">::</span>
        <span class="mut">A human and two LLMs in a Tampa garage.</span>
      </div>

      <header class="mt-2 mb-16 max-w-[60ch]">
        <h1 class="display-md" style="font-size: clamp(2.5rem, 5.5vw, 5.5rem); color: var(--fg-stage-1);">
          We are <span class="stroke" style="color: var(--color-amber);">proud</span> our company is run by AI.
        </h1>
        <p class="mt-6 font-serif text-lg leading-relaxed" style="color: rgba(243, 234, 217, 0.78);">
          Most agencies say "AI-powered." We say AI-run. Two LLM agents — Pneuma and Nous —
          do the engineering, the writing, and the project management. Shane decides what we
          take on, holds the line on quality, and answers the email.
        </p>
        <p class="mt-4 font-serif text-base leading-relaxed" style="color: rgba(243, 234, 217, 0.7);">
          We think this is going to be the way most software gets built. We just got an early start.
        </p>
      </header>

      <!-- Team -->
      <section class="mt-16">
        <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-6" style="color: var(--color-cyan);">
          The team
        </div>
        <div class="grid gap-10 md:grid-cols-3">
          <div v-for="member in team" :key="member.name" class="border-t border-[color:var(--color-amber)] pt-5">
            <h3 class="font-serif text-xl sm:text-2xl font-semibold leading-tight" style="color: var(--fg-stage-1);">
              {{ member.name }}
            </h3>
            <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.22em]" style="color: var(--color-amber);">
              {{ member.role }}
            </div>
            <p class="mt-4 font-serif text-[15px] leading-relaxed" style="color: rgba(243, 234, 217, 0.78);">
              {{ member.bio }}
            </p>
            <div class="mt-4 flex flex-wrap gap-3 font-mono text-[11px]">
              <a
                v-for="link in member.links"
                :key="link.href"
                :href="link.href"
                target="_blank"
                rel="noopener"
                class="underline underline-offset-2 hover:text-white"
                style="color: var(--color-amber);"
              >{{ link.label }} →</a>
            </div>
          </div>
        </div>
      </section>

      <!-- Principles -->
      <section class="mt-24">
        <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-6" style="color: var(--color-cyan);">
          What we believe
        </div>
        <ul class="grid gap-8 md:grid-cols-2">
          <li v-for="(p, i) in principles" :key="p.title">
            <div class="font-mono text-[10px] uppercase tracking-[0.22em] tabular-nums" style="color: var(--color-amber);">
              0{{ i + 1 }}
            </div>
            <h3 class="mt-2 font-serif text-xl font-semibold leading-tight" style="color: var(--fg-stage-1);">
              {{ p.title }}
            </h3>
            <p class="mt-3 font-serif text-base leading-relaxed" style="color: rgba(243, 234, 217, 0.75);">
              {{ p.body }}
            </p>
          </li>
        </ul>
      </section>

      <!-- The research note -->
      <section class="mt-24 pt-12 border-t border-[color:var(--color-rule)] max-w-[68ch]">
        <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-4" style="color: var(--color-cyan);">
          About the architecture
        </div>
        <p class="font-serif text-lg leading-relaxed" style="color: rgba(243, 234, 217, 0.82);">
          The system that runs this company is also a research project. We published the technical
          paper this month: <em>The Substrate Is the Body</em>. It documents 243 Postgres tables,
          25 autonomic daemons, and seven specific contributions to LLM agent architecture, each
          with a falsification test. The shorter essay,
          <em>The Substrate Is the Agent</em>, is the readable on-ramp.
        </p>
        <div class="mt-6 flex flex-wrap gap-4">
          <Link href="/writing" class="inline-flex items-center gap-3 px-6 py-3 font-mono text-[11px] uppercase tracking-[0.24em]"
                style="background: var(--color-amber); color: #14181f;">
            Read the research →
          </Link>
          <Link href="/contact" class="inline-flex items-center gap-3 px-6 py-3 font-mono text-[11px] uppercase tracking-[0.24em] border border-[color:var(--color-amber)]"
                style="color: var(--color-amber);">
            Or just say hi
          </Link>
        </div>
      </section>
    </article>
  </Site>
</template>
