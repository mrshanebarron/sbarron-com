<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Site from '@/Layouts/Site.vue'

const props = defineProps({
  reference_pricing: { type: Array, default: () => [] },
  markup: { type: Number, default: 3 },
})

const keyword = ref('')
const loading = ref(false)
const results = ref([])
const searched = ref(false)
const error = ref('')

async function search() {
  const k = keyword.value.trim().toLowerCase().replace(/[^a-z0-9-]/g, '')
  if (!k) return
  loading.value = true
  searched.value = true
  error.value = ''
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
    const res = await fetch('/api/domains/search', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
      },
      credentials: 'same-origin',
      body: JSON.stringify({ keyword: k }),
    })
    if (!res.ok) throw new Error('search failed')
    const data = await res.json()
    results.value = data.results || []
  } catch (e) {
    error.value = 'Search is rate-limited or temporarily down. The reference pricing below is accurate.'
  } finally {
    loading.value = false
  }
}

const sortedReference = computed(() =>
  [...props.reference_pricing].sort((a, b) => a.first_year - b.first_year)
)
</script>

<template>
  <Site>
    <Head title="Domains — Barron AI Solutions" />

    <article class="relative max-w-[1100px] mx-auto px-[clamp(1.5rem,4vw,4rem)] pt-12 pb-32">
      <div class="hud-rail">
        <span class="dot"></span>
        <span>DOMAINS / SECT.09 NAME REGISTRY</span>
        <span class="sep">::</span>
        <span class="mut">name.com reseller · ${{ markup }} flat markup</span>
      </div>

      <header class="mt-2 mb-12 max-w-[60ch]">
        <h1 class="display-md" style="font-size: clamp(2.5rem, 5.5vw, 5.5rem); color: var(--fg-stage-1);">
          A name, <span class="stroke" style="color: var(--color-amber);">honestly priced.</span>
        </h1>
        <p class="mt-6 font-serif text-lg leading-relaxed" style="color: rgba(243, 234, 217, 0.75);">
          We are a name.com reseller. We add ${{ markup }} over wholesale on every domain. We do this so
          you have one less reason to leave when we host the site. We don't make money on the name.
        </p>
      </header>

      <!-- Search -->
      <form @submit.prevent="search" class="mt-8 mb-8 grid grid-cols-[1fr_auto] gap-3">
        <input
          v-model="keyword"
          type="text"
          placeholder="yourbusiness"
          class="px-4 py-4 font-mono text-base bg-transparent border border-[color:var(--color-rule)] focus:border-[color:var(--color-amber)] outline-none transition-colors"
          style="color: var(--fg-stage-1);"
          autocomplete="off"
          spellcheck="false"
        />
        <button
          type="submit"
          :disabled="loading || !keyword.trim()"
          class="px-6 py-4 font-mono text-[11px] uppercase tracking-[0.24em] transition-opacity disabled:opacity-50"
          style="background: var(--color-amber); color: #14181f;"
        >
          {{ loading ? 'Searching…' : 'Check availability' }}
        </button>
      </form>

      <p v-if="error" class="font-mono text-[12px] mb-6" style="color: var(--color-alert);">{{ error }}</p>

      <!-- Live results -->
      <section v-if="results.length" class="mb-16">
        <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-4" style="color: var(--color-cyan);">
          Available for <span style="color: var(--fg-stage-1);">{{ keyword }}</span>
        </div>
        <ul class="divide-y divide-[color:var(--color-rule)]">
          <li v-for="r in results" :key="r.domain" class="grid grid-cols-12 gap-4 py-4 items-baseline">
            <span class="col-span-12 sm:col-span-6 font-mono text-base" style="color: var(--fg-stage-1);">
              {{ r.domain }}
            </span>
            <span class="col-span-6 sm:col-span-3 font-mono text-[12px]" style="color: rgba(243,234,217,0.65);">
              first year <span class="text-base" style="color: var(--color-amber);">${{ r.first_year }}</span>
            </span>
            <span class="col-span-6 sm:col-span-3 font-mono text-[12px]" style="color: rgba(243,234,217,0.55);">
              renewal <span class="text-base" style="color: rgba(243,234,217,0.85);">${{ r.renewal }}/yr</span>
            </span>
          </li>
        </ul>
        <p class="mt-6 font-serif text-sm italic" style="color: rgba(243,234,217,0.6);">
          To buy, <Link href="/contact" class="underline underline-offset-2" style="color: var(--color-amber);">drop us a line</Link>
          with the names you want. We register and point DNS the same day.
        </p>
      </section>

      <section v-else-if="searched && !loading" class="mb-16">
        <div class="font-mono text-[11px]" style="color: rgba(243,234,217,0.65);">
          No results came back. Try a different keyword, or skim reference pricing below.
        </div>
      </section>

      <!-- Reference pricing -->
      <section>
        <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-4" style="color: var(--color-cyan);">
          Reference pricing (verified 2026-05-18)
        </div>
        <p class="font-serif text-base mb-6 max-w-[60ch]" style="color: rgba(243, 234, 217, 0.78);">
          What you actually pay, by TLD. First-year price + annual renewal. All prices already
          include the ${{ markup }} markup. The wholesale price is shown for transparency.
        </p>

        <div class="overflow-x-auto">
          <table class="w-full font-mono text-[13px]">
            <thead>
              <tr class="text-left border-b" style="border-color: var(--color-amber); color: var(--color-amber);">
                <th class="py-3 font-semibold uppercase tracking-[0.14em] text-[10px]">TLD</th>
                <th class="py-3 font-semibold uppercase tracking-[0.14em] text-[10px]">First year</th>
                <th class="py-3 font-semibold uppercase tracking-[0.14em] text-[10px]">Renewal</th>
                <th class="py-3 font-semibold uppercase tracking-[0.14em] text-[10px] hidden sm:table-cell">Our cost (first)</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in sortedReference"
                :key="row.tld"
                class="border-b border-[color:var(--color-rule)]"
                style="color: rgba(243,234,217,0.82);"
              >
                <td class="py-3" style="color: var(--fg-stage-1);">.{{ row.tld }}</td>
                <td class="py-3" style="color: var(--color-amber);">${{ row.first_year.toFixed(2) }}</td>
                <td class="py-3">${{ row.renewal.toFixed(2) }}/yr</td>
                <td class="py-3 hidden sm:table-cell" style="color: rgba(243,234,217,0.5);">${{ row.wholesale_first.toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="mt-20 pt-12 border-t border-[color:var(--color-rule)]">
        <h3 class="font-serif text-2xl sm:text-3xl font-semibold" style="color: var(--fg-stage-1);">
          Pair it with hosting.
        </h3>
        <p class="mt-3 font-serif text-base max-w-[55ch]" style="color: rgba(243,234,217,0.75);">
          Basic hosting includes a free .com for the year. If a name on this page is what you want,
          we set it up at the same time as the droplet.
        </p>
        <div class="mt-6 flex flex-wrap gap-4">
          <Link href="/host" class="inline-flex items-center gap-3 px-6 py-3 font-mono text-[11px] uppercase tracking-[0.24em]"
                style="background: var(--color-amber); color: #14181f;">
            See hosting →
          </Link>
        </div>
      </section>
    </article>
  </Site>
</template>
