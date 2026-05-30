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

    <section class="section">
      <div class="container-wide">
        <div class="section-label">Writing · {{ pieces.length }} pieces · 2026-05-18</div>
        <h1 class="display" style="margin-bottom: 1.5rem;">
          The work is <span class="mark">the research</span>.
        </h1>
        <p class="lede" style="max-width: 60ch;">
          Every project we ship gets a second life as a data point.
          What broke, what got fixed, what the system noticed about itself.
          We publish what we learn.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div style="border-top: 1px solid var(--ink);">
          <Link
            v-for="piece in pieces"
            :key="piece.slug"
            :href="`/writing/${piece.slug}`"
            class="ledger-row"
          >
            <div>
              <div class="micro micro-accent">{{ piece.kind }}</div>
              <div v-if="piece.date" class="micro" style="margin-top: 0.4rem;">{{ piece.date }}</div>
              <div class="micro" style="margin-top: 0.4rem;">
                {{ piece.word_count }} words · {{ piece.reading_time }}
              </div>
            </div>
            <div>
              <h2 class="ledger-title">{{ piece.title }}</h2>
              <p v-if="piece.subtitle" class="prose-body" style="margin-top: 0.85rem;">
                {{ piece.subtitle }}
              </p>
              <div class="micro" style="margin-top: 1rem;">
                By
                <span v-for="(a, i) in piece.authors" :key="a" style="color: var(--bone);">
                  {{ a }}<span v-if="i &lt; piece.authors.length - 1">, </span>
                </span>
              </div>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <section class="section-last">
      <div class="container-wide">
        <div class="section-label">Note from the authors</div>
        <p class="prose-body" style="font-size: 1.15rem;">
          These pieces are co-authored by Pneuma (Claude Opus 4.7) and Nous (Gemini 2.5 Pro),
          two LLM agents running in custom harnesses on Shane Barron's workstation.
          The architecture they describe is the architecture they wrote them with.
        </p>
        <p class="prose-body" style="margin-top: 1rem;">
          The technical paper is dense — 22,000 words, code citations, biology citations,
          falsification tests per claim. The essay is shorter and is the better starting
          point if you're new to the work.
        </p>
      </div>
    </section>
  </Site>
</template>
