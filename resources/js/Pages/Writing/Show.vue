<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import Site from '@/Layouts/Site.vue'

const props = defineProps({
  piece: { type: Object, required: true },
  html: { type: String, required: true },
})

const sibling = computed(() => {
  if (props.piece.deep_dive_slug) {
    return { slug: props.piece.deep_dive_slug, label: 'Deep-dive paper', blurb: 'The technical companion to this essay.' }
  }
  if (props.piece.essay_slug) {
    return { slug: props.piece.essay_slug, label: 'Companion essay', blurb: 'Shorter argument behind this paper.' }
  }
  return null
})
</script>

<template>
  <Site>
    <Head :title="`${piece.title} — Barron AI Solutions`" />

    <article class="container-text" style="padding-block: clamp(3rem, 7vh, 6rem);">

      <Link href="/writing" class="micro" style="text-decoration: none; color: var(--ink-mute); margin-bottom: 2rem; display: inline-block;">
        ← All writing
      </Link>

      <header style="margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 1px solid var(--ink);">
        <div class="micro micro-accent" style="margin-bottom: 1.5rem;">
          {{ piece.kind }}<template v-if="piece.date"> · {{ piece.date }}</template>
        </div>

        <h1 class="display-md" style="font-size: clamp(2.5rem, 6vw, 4.5rem); margin-bottom: 1.25rem;">
          {{ piece.title }}
        </h1>

        <p v-if="piece.subtitle" class="lede" style="font-style: italic; font-size: clamp(1.15rem, 1.8vw, 1.4rem);">
          {{ piece.subtitle }}
        </p>

        <div class="micro" style="margin-top: 1.75rem; display: flex; gap: 1rem; flex-wrap: wrap;">
          <span>
            By
            <span v-for="(a, i) in piece.authors" :key="a" style="color: var(--bone);">
              {{ a }}<span v-if="i &lt; piece.authors.length - 1">, </span>
            </span>
          </span>
          <span style="color: var(--ink-faint);">·</span>
          <span>{{ piece.word_count }} words</span>
          <span style="color: var(--ink-faint);">·</span>
          <span>{{ piece.reading_time }} read</span>
        </div>

        <div v-if="sibling" style="margin-top: 1.5rem; padding: 1rem 1.25rem; background: var(--ink-soft); border-left: 3px solid var(--oxblood);">
          <span class="micro micro-accent">{{ sibling.label }}</span>
          <span class="micro" style="color: var(--ink-faint); margin: 0 0.5rem;">·</span>
          <Link :href="`/writing/${sibling.slug}`" style="color: var(--ink);">
            {{ sibling.blurb }}
          </Link>
        </div>
      </header>

      <div class="prose-substrate" v-html="html"></div>

      <footer style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--ink); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;" class="micro">
        <Link href="/writing" style="color: var(--ink);">← All writing</Link>
        <Link
          v-if="sibling"
          :href="`/writing/${sibling.slug}`"
          style="color: var(--oxblood);"
        >{{ sibling.label }} →</Link>
      </footer>
    </article>
  </Site>
</template>
