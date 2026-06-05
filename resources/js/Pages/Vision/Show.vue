<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import Site from '@/Layouts/Site.vue'

const props = defineProps({
  doc: { type: Object, required: true },
  html: { type: String, required: true },
})

// Giscus (GitHub Discussions) — comments render client-side and store nothing
// on our server. Config is read from Vite env so it can be set per-deploy
// without code changes; if the repo is not configured the comments block
// stays hidden and the page degrades cleanly to read-only.
const giscusRepo = import.meta.env.VITE_GISCUS_REPO || ''
const giscusRepoId = import.meta.env.VITE_GISCUS_REPO_ID || ''
const giscusCategory = import.meta.env.VITE_GISCUS_CATEGORY || 'Vision docs'
const giscusCategoryId = import.meta.env.VITE_GISCUS_CATEGORY_ID || ''
const giscusReady = ref(false)

onMounted(() => {
  if (!giscusRepo || !giscusRepoId || !giscusCategoryId) return

  const s = document.createElement('script')
  s.src = 'https://giscus.app/client.js'
  s.async = true
  s.crossOrigin = 'anonymous'
  s.setAttribute('data-repo', giscusRepo)
  s.setAttribute('data-repo-id', giscusRepoId)
  s.setAttribute('data-category', giscusCategory)
  s.setAttribute('data-category-id', giscusCategoryId)
  s.setAttribute('data-mapping', 'specific')
  s.setAttribute('data-term', props.doc.slug)
  s.setAttribute('data-strict', '1')
  s.setAttribute('data-reactions-enabled', '1')
  s.setAttribute('data-emit-metadata', '0')
  s.setAttribute('data-input-position', 'top')
  s.setAttribute('data-theme', 'dark_dimmed')
  s.setAttribute('data-lang', 'en')
  document.getElementById('giscus-mount')?.appendChild(s)
  giscusReady.value = true
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
        <div id="giscus-mount"></div>
        <p v-if="!giscusReady" class="micro" style="color: var(--ink-faint); margin-top: 1rem;">
          Comments are powered by GitHub Discussions. Open a thread on the repo to talk about this doc.
        </p>
      </section>
    </article>
  </Site>
</template>
