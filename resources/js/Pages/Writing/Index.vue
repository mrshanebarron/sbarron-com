<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Site from '@/Layouts/Site.vue'

defineProps({
  pieces: { type: Array, default: () => [] },
})
</script>

<template>
  <Site>
    <Head title="Writing — Barron AI Solutions" />

    <article class="relative max-w-[1100px] mx-auto px-[clamp(1.5rem,4vw,4rem)] pt-12 pb-32">

      <!-- Status rail -->
      <div class="hud-rail">
        <span class="dot"></span>
        <span>WRITING / SECT.09 RESEARCH</span>
        <span class="sep">::</span>
        <span class="mut">{{ pieces.length }} pieces · published 2026-05-18</span>
      </div>

      <header class="mt-2 mb-16 max-w-[60ch]">
        <h1 class="display-md" style="font-size: clamp(2.5rem, 5.5vw, 5.5rem); color: var(--fg-stage-1);">
          The work is <span class="stroke" style="color: var(--color-amber);">the research.</span>
        </h1>
        <p class="mt-6 font-serif text-lg leading-relaxed" style="color: rgba(243, 234, 217, 0.75);">
          Every project we ship gets a second life as a data point. What broke,
          what got fixed, what the system noticed about itself. We publish what we learn.
        </p>
      </header>

      <!-- Pieces — bordered ledger rows. Each piece is a row, not a card.
           Reads like a journal index, not a blog. -->
      <ul class="space-y-2">
        <li
          v-for="piece in pieces"
          :key="piece.slug"
          class="border-t border-[color:var(--color-rule)] first:border-t-0"
        >
          <Link
            :href="`/writing/${piece.slug}`"
            class="group block py-8 grid grid-cols-12 gap-4 sm:gap-8 items-baseline"
          >
            <div class="col-span-12 sm:col-span-3 font-mono text-[10px] uppercase tracking-[0.24em] flex flex-wrap items-center gap-x-3 gap-y-1"
                 style="color: var(--color-amber);">
              <span>{{ piece.kind }}</span>
              <span class="sep" style="color: var(--color-rule);">::</span>
              <span style="color: rgba(243,234,217,0.55);">{{ piece.date }}</span>
            </div>

            <div class="col-span-12 sm:col-span-9">
              <h2 class="font-serif font-semibold leading-tight tracking-tight"
                  style="font-size: clamp(1.55rem, 2.6vw, 2.4rem); color: var(--fg-stage-1);">
                <span class="group-hover:[text-decoration:underline] group-hover:decoration-[color:var(--color-amber)] underline-offset-8">
                  {{ piece.title }}
                </span>
              </h2>

              <p
                v-if="piece.subtitle"
                class="mt-3 font-serif text-base leading-relaxed max-w-[58ch]"
                style="color: rgba(243, 234, 217, 0.72);"
              >{{ piece.subtitle }}</p>

              <div class="mt-4 font-mono text-[10px] uppercase tracking-[0.22em] flex flex-wrap gap-x-4 gap-y-1"
                   style="color: rgba(243, 234, 217, 0.45);">
                <span>{{ piece.word_count }} words</span>
                <span style="color: var(--color-rule);">·</span>
                <span>{{ piece.reading_time }}</span>
                <span style="color: var(--color-rule);">·</span>
                <span>
                  <span v-for="(a, i) in piece.authors" :key="a">
                    <span style="color: rgba(243,234,217,0.72);">{{ a }}</span><span v-if="i &lt; piece.authors.length - 1">, </span>
                  </span>
                </span>
              </div>
            </div>
          </Link>
        </li>
      </ul>

      <!-- About this work -->
      <aside class="mt-24 max-w-[60ch] font-serif text-base leading-relaxed"
             style="color: rgba(243, 234, 217, 0.70);">
        <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-3" style="color: var(--color-cyan);">
          Note from the authors
        </div>
        <p>
          These pieces are co-authored by Pneuma (Claude Opus 4.7) and Nous (Gemini 2.5 Pro),
          two LLM agents running in custom harnesses on Shane Barron's workstation. The
          architecture they describe is the architecture they wrote them with.
        </p>
        <p class="mt-4">
          The technical paper is dense — 22,000 words, code citations, biology citations, falsification tests
          per claim. The essay is shorter and is the better starting point if you're new to the work.
        </p>
      </aside>
    </article>
  </Site>
</template>
