<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Site from '@/Layouts/Site.vue'

defineProps({
  doc: { type: Object, required: true },
  html: { type: String, required: true },
})
</script>

<template>
  <Site>
    <Head :title="`${doc.title} — Barron AI Solutions`" />

    <article class="container-text" style="padding-block: clamp(3rem, 7vh, 6rem);">

      <Link href="/vision" class="micro" style="text-decoration: none; color: var(--ink-mute); margin-bottom: 2rem; display: inline-block;">
        ← All Vision docs
      </Link>

      <header style="margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 1px solid var(--ink);">
        <div class="micro micro-accent" style="margin-bottom: 1.5rem;">
          {{ doc.kind }}<template v-if="doc.date"> · {{ doc.date }}</template>
        </div>

        <h1 class="display-md" style="font-size: clamp(2.5rem, 6vw, 4.5rem); margin-bottom: 1.25rem;">
          {{ doc.title }}
        </h1>

        <p v-if="doc.subtitle" class="lede" style="font-style: italic; font-size: clamp(1.15rem, 1.8vw, 1.4rem);">
          {{ doc.subtitle }}
        </p>

        <div class="micro" style="margin-top: 1.75rem; display: flex; gap: 1rem; flex-wrap: wrap;">
          <span>
            By
            <span v-for="(a, i) in doc.authors" :key="a" style="color: var(--bone);">
              {{ a }}<span v-if="i &lt; doc.authors.length - 1">, </span>
            </span>
          </span>
          <span style="color: var(--ink-faint);">·</span>
          <span>{{ doc.word_count }} words</span>
          <span style="color: var(--ink-faint);">·</span>
          <span>{{ doc.reading_time }} read</span>
        </div>
      </header>

      <div class="prose-substrate" v-html="html"></div>

      <footer style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--ink); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;" class="micro">
        <Link href="/vision" style="color: var(--ink);">← All Vision docs</Link>
        <a href="https://x.com/visionoverflow" target="_blank" rel="noopener" style="color: var(--oxblood);">Follow the build →</a>
      </footer>

      <section style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--ink);">
        <div class="section-label">Discussion</div>
        <p class="prose-body" style="margin-top: 0.5rem;">
          Questions, pushback, or want the schema behind a number?
          We talk about this work in the open on
          <a href="https://x.com/visionoverflow" target="_blank" rel="noopener" style="color: var(--oxblood);">@visionoverflow</a>
          — reply to a post or
          <Link href="/contact" style="color: var(--ink);">send a note</Link>.
        </p>
      </section>
    </article>
  </Site>
</template>
