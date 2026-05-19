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
  { key: 'all',       label: 'All' },
  { key: 'ai',        label: 'AI' },
  { key: 'saas',      label: 'SaaS' },
  { key: 'services',  label: 'Services' },
  { key: 'trades',    label: 'Trades' },
  { key: 'editorial', label: 'Editorial' },
  { key: 'ecom',      label: 'eCommerce' },
]

const allWork = computed(() => {
  const c = (props.clients || []).map(x => ({ ...x, featured: true, category: x.category || 'live' }))
  const m = (props.mvps || []).map(x => ({ ...x, featured: false }))
  const out = []
  let ci = 0, mi = 0
  while (ci < c.length || mi < m.length) {
    if (ci < c.length) out.push(c[ci++])
    for (let k = 0; k < 3 && mi < m.length; k++) out.push(m[mi++])
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

    <section class="section">
      <div class="container-wide">
        <div class="section-label">Portfolio · live + MVPs</div>
        <h1 class="display" style="margin-bottom: 1.5rem;">
          The <span class="mark">work</span>.
        </h1>
        <p class="lede" style="max-width: 60ch;">
          Every site here was built end to end — brief, spec, scaffold, ship.
          Featured items are paying clients on their own infrastructure.
          The rest are MVPs that show the range.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div class="filter-row">
          <button
            v-for="f in filters"
            :key="f.key"
            @click="filter = f.key"
            class="filter-pill"
            :class="{ 'is-active': filter === f.key }"
          >{{ f.label }}</button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 2rem 1.5rem;">
          <a
            v-for="item in filtered"
            :key="item.slug"
            :href="item.url"
            target="_blank"
            rel="noopener"
            class="work"
          >
            <div class="work-shot">
              <img v-if="item.image" :src="item.image" :alt="item.name" loading="lazy" />
            </div>
            <div class="work-body">
              <div class="work-name">{{ item.name }}</div>
              <div class="work-kind">{{ item.kind }}</div>
              <p v-if="item.featured" class="work-summary">{{ item.summary }}</p>
              <span v-if="item.featured" class="work-live">— Live in production</span>
            </div>
          </a>
        </div>

        <p v-if="!filtered.length" class="prose-body" style="margin-top: 2rem; font-style: italic;">
          Nothing in that filter yet — try All.
        </p>
      </div>
    </section>

    <section class="section-last">
      <div class="container-wide">
        <h2 class="display-md" style="margin-bottom: 1rem;">See yourself on this page?</h2>
        <p class="lede" style="max-width: 52ch; margin-bottom: 2rem;">
          Drop us a line with your brief.
          We will read it, name what's missing, and tell you what it costs.
        </p>
        <Link href="/contact" class="btn btn-primary">Start a project →</Link>
      </div>
    </section>
  </Site>
</template>
