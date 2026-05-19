<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Site from '@/Layouts/Site.vue'

const props = defineProps({
  clients: { type: Array, default: () => [] },
  mvps: { type: Array, default: () => [] },
})

const filter = ref('all')
const filters = [
  { key: 'all',       label: 'All work' },
  { key: 'ai',        label: 'AI' },
  { key: 'saas',      label: 'SaaS' },
  { key: 'services',  label: 'Services' },
  { key: 'trades',    label: 'Trades' },
  { key: 'editorial', label: 'Editorial' },
  { key: 'ecom',      label: 'eCommerce' },
]

const allWork = computed(() => {
  const clientItems = (props.clients || []).map(c => ({
    slug: c.slug,
    name: c.name,
    kind: c.kind,
    summary: c.summary,
    url: c.url,
    image: c.image,
    category: c.category || 'live',
    featured: true,
  }))
  const mvpItems = (props.mvps || []).map(m => ({ ...m, featured: false }))
  const out = []
  let ci = 0, mi = 0
  while (ci < clientItems.length || mi < mvpItems.length) {
    if (ci < clientItems.length) out.push(clientItems[ci++])
    for (let k = 0; k < 3 && mi < mvpItems.length; k++) out.push(mvpItems[mi++])
  }
  return out
})

const filtered = computed(() => {
  if (filter.value === 'all') return allWork.value
  return allWork.value.filter(w => w.category === filter.value)
})
</script>

<template>
  <Site>
    <Head title="Portfolio — Barron AI Solutions" />

    <article class="relative max-w-[1400px] mx-auto px-[clamp(1.5rem,4vw,4rem)] pt-12 pb-32">
      <div class="hud-rail">
        <span class="dot"></span>
        <span>PORTFOLIO / SECT.09 CATALOG</span>
        <span class="sep">::</span>
        <span class="mut">Live clients + MVPs — same shelf, no distinction in pride</span>
      </div>

      <header class="mt-2 mb-12 max-w-[60ch]">
        <h1 class="display-md" style="font-size: clamp(2.5rem, 5.5vw, 5.5rem); color: var(--fg-stage-1);">
          The <span class="stroke" style="color: var(--color-amber);">work.</span>
        </h1>
        <p class="mt-6 font-serif text-lg leading-relaxed" style="color: rgba(243, 234, 217, 0.75);">
          Every site here was built end to end — brief, spec, scaffold, ship. Featured items
          are paying clients on their own infrastructure. The rest are MVPs that show the range.
        </p>
      </header>

      <!-- Filter -->
      <div class="mb-10 flex flex-wrap gap-1">
        <button
          v-for="f in filters"
          :key="f.key"
          @click="filter = f.key"
          class="port-filter-pill"
          :class="{ 'port-filter-active': filter === f.key }"
        >{{ f.label }}</button>
      </div>

      <!-- Grid -->
      <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <a
          v-for="item in filtered"
          :key="item.slug"
          :href="item.url"
          target="_blank"
          rel="noopener"
          class="port-card"
          :class="{ 'port-card-featured': item.featured }"
        >
          <div class="port-card-shot">
            <img v-if="item.image" :src="item.image" :alt="item.name" loading="lazy" />
            <span v-if="item.featured" class="port-card-live">Live client</span>
          </div>
          <div class="port-card-body">
            <h4 class="text-base">{{ item.name }}</h4>
            <p class="mt-1.5 text-sm leading-snug">{{ item.summary }}</p>
            <span class="mt-3 inline-block font-mono text-[10px] uppercase tracking-[0.22em]">{{ item.kind }}</span>
          </div>
        </a>
      </div>

      <section v-if="!filtered.length" class="mt-12 font-serif text-base" style="color: rgba(243,234,217,0.6);">
        Nothing in that filter yet — try All.
      </section>

      <section class="mt-20 pt-12 border-t border-[color:var(--color-rule)]">
        <h3 class="font-serif text-2xl sm:text-3xl font-semibold" style="color: var(--fg-stage-1);">
          See yourself on this page?
        </h3>
        <p class="mt-3 font-serif text-base max-w-[55ch]" style="color: rgba(243,234,217,0.75);">
          Drop us a line with your brief. We will read it, name what's missing, and tell you what it costs.
        </p>
        <div class="mt-6 flex flex-wrap gap-4">
          <Link href="/contact" class="inline-flex items-center gap-3 px-6 py-3 font-mono text-[11px] uppercase tracking-[0.24em]"
                style="background: var(--color-amber); color: #14181f;">
            Start a project →
          </Link>
        </div>
      </section>
    </article>
  </Site>
</template>
