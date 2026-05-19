<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import Site from '@/Layouts/Site.vue'

const props = defineProps({
  piece: { type: Object, required: true },
  html: { type: String, required: true },
})

const sibling = computed(() => {
  // Cross-link essay ↔ paper at the top.
  if (props.piece.deep_dive_slug) {
    return {
      slug: props.piece.deep_dive_slug,
      label: 'Deep-dive paper',
      blurb: 'The technical companion to this essay.',
    }
  }
  if (props.piece.essay_slug) {
    return {
      slug: props.piece.essay_slug,
      label: 'Companion essay',
      blurb: 'Shorter argument behind this paper.',
    }
  }
  return null
})
</script>

<template>
  <Site>
    <Head :title="`${piece.title} — Barron AI Solutions`" />

    <article class="relative max-w-[760px] mx-auto px-[clamp(1.5rem,4vw,3rem)] pt-12 pb-32">

      <!-- Status rail -->
      <div class="hud-rail">
        <Link href="/writing" class="hover:text-white" style="color: var(--color-amber);">
          ← Writing
        </Link>
        <span class="sep">::</span>
        <span class="mut">{{ piece.kind }} · {{ piece.date }}</span>
      </div>

      <!-- Title block -->
      <header class="mt-6 mb-12">
        <h1
          class="font-serif font-semibold tracking-tight leading-[1.05]"
          style="font-size: clamp(2.25rem, 5.5vw, 4.5rem); color: var(--fg-stage-1);"
        >{{ piece.title }}</h1>

        <p
          v-if="piece.subtitle"
          class="mt-5 font-serif text-xl italic leading-snug"
          style="color: rgba(243, 234, 217, 0.75);"
        >{{ piece.subtitle }}</p>

        <div class="mt-8 pt-5 border-t border-[color:var(--color-rule)] flex flex-wrap items-center gap-x-5 gap-y-2 font-mono text-[10px] uppercase tracking-[0.22em]"
             style="color: rgba(243, 234, 217, 0.55);">
          <span>
            By
            <span v-for="(a, i) in piece.authors" :key="a">
              <span style="color: var(--color-amber);">{{ a }}</span><span v-if="i &lt; piece.authors.length - 1">, </span>
            </span>
          </span>
          <span style="color: var(--color-rule);">·</span>
          <span>{{ piece.word_count }} words</span>
          <span style="color: var(--color-rule);">·</span>
          <span>{{ piece.reading_time }} read</span>
        </div>

        <aside
          v-if="sibling"
          class="mt-6 px-4 py-3 font-mono text-[11px]"
          style="background: rgba(255, 154, 60, 0.06); color: rgba(243, 234, 217, 0.7);"
        >
          <span class="uppercase tracking-[0.22em]" style="color: var(--color-amber);">{{ sibling.label }}</span>
          <span style="color: var(--color-rule);"> :: </span>
          <Link :href="`/writing/${sibling.slug}`" class="underline underline-offset-2 hover:text-white">
            {{ sibling.blurb }}
          </Link>
        </aside>
      </header>

      <!-- Article body. CommonMark output gets editorial typography. -->
      <div
        class="prose-substrate"
        v-html="html"
      ></div>

      <!-- Footer / signoff -->
      <footer class="mt-20 pt-8 border-t border-[color:var(--color-rule)] font-mono text-[11px] uppercase tracking-[0.22em]"
              style="color: rgba(243, 234, 217, 0.5);">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <Link href="/writing" class="hover:text-white">← All writing</Link>
          <Link
            v-if="sibling"
            :href="`/writing/${sibling.slug}`"
            class="hover:text-white"
            style="color: var(--color-amber);"
          >{{ sibling.label }} →</Link>
        </div>
      </footer>
    </article>
  </Site>
</template>

<style>
/* Editorial typography for the long-form body. Scoped intentionally
   global so v-html'd markup picks up the rules. */
.prose-substrate {
  font-family: var(--font-serif);
  font-size: 1.075rem;
  line-height: 1.65;
  color: rgba(243, 234, 217, 0.88);
  font-weight: 400;
}
.prose-substrate > * + * { margin-top: 1.4em; }
.prose-substrate p { text-wrap: pretty; }

.prose-substrate h2 {
  font-family: var(--font-serif);
  font-weight: 700;
  font-size: clamp(1.5rem, 2.5vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.015em;
  margin-top: 3rem;
  color: var(--fg-stage-1);
}
.prose-substrate h3 {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.35rem;
  margin-top: 2.5rem;
  color: var(--fg-stage-1);
}
.prose-substrate h4 {
  font-family: var(--font-mono);
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 0.75rem;
  margin-top: 2rem;
  color: var(--color-amber);
}
.prose-substrate strong { color: var(--fg-stage-1); font-weight: 600; }
.prose-substrate em { color: rgba(243, 234, 217, 0.95); }

.prose-substrate a {
  color: var(--color-amber);
  text-decoration: underline;
  text-decoration-thickness: 1px;
  text-underline-offset: 3px;
  transition: color 160ms ease;
}
.prose-substrate a:hover { color: #ffb850; }

.prose-substrate blockquote {
  margin: 2rem 0;
  padding: 0.25rem 0 0.25rem 1.5rem;
  border-left: 2px solid var(--color-amber);
  font-style: italic;
  color: rgba(243, 234, 217, 0.85);
  font-size: 1.05em;
  line-height: 1.55;
}
.prose-substrate blockquote p { margin: 0; }
.prose-substrate blockquote + blockquote { margin-top: -0.5rem; }

.prose-substrate ul, .prose-substrate ol {
  padding-left: 1.6rem;
}
.prose-substrate li + li { margin-top: 0.4rem; }
.prose-substrate ul li { list-style: '— '; }
.prose-substrate ul li::marker {
  color: var(--color-amber);
  font-family: var(--font-mono);
}
.prose-substrate ol li { list-style: decimal; }
.prose-substrate ol li::marker {
  color: var(--color-amber);
  font-family: var(--font-mono);
  font-size: 0.85em;
  font-weight: 600;
}

.prose-substrate hr {
  border: none;
  margin: 3rem 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--color-rule), transparent);
}

/* Code — mono. Inline + blocks. */
.prose-substrate code {
  font-family: var(--font-mono);
  font-size: 0.9em;
  padding: 0.1em 0.4em;
  background: rgba(255, 154, 60, 0.08);
  color: var(--color-amber);
  border-radius: 2px;
}
.prose-substrate pre {
  font-family: var(--font-mono);
  font-size: 0.85em;
  line-height: 1.55;
  padding: 1.25rem 1.4rem;
  background: rgba(20, 24, 31, 0.55);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  overflow-x: auto;
  margin: 1.8rem 0;
  border-left: 2px solid var(--color-amber);
  color: rgba(243, 234, 217, 0.9);
}
.prose-substrate pre code {
  background: transparent;
  color: inherit;
  padding: 0;
  font-size: 1em;
}

/* Tables — substrate evidence reads as data, not prose. */
.prose-substrate table {
  width: 100%;
  margin: 2rem 0;
  border-collapse: collapse;
  font-family: var(--font-mono);
  font-size: 0.82rem;
}
.prose-substrate th,
.prose-substrate td {
  text-align: left;
  padding: 0.65rem 0.75rem;
  border-bottom: 1px solid var(--color-rule);
  vertical-align: top;
}
.prose-substrate th {
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.7rem;
  color: var(--color-amber);
  border-bottom-color: var(--color-amber);
}
.prose-substrate td { color: rgba(243, 234, 217, 0.82); }
.prose-substrate td strong { color: var(--fg-stage-1); }
</style>
