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
    error.value = 'Search is rate-limited or temporarily down. Reference pricing below is current.'
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

    <section class="section">
      <div class="container-wide">
        <div class="section-label">Domain registration · name.com reseller</div>
        <h1 class="display" style="margin-bottom: 1.5rem;">
          A name, <span class="mark">honestly</span> priced.
        </h1>
        <p class="lede" style="max-width: 60ch;">
          We are a name.com reseller. We add ${{ markup }} over wholesale on every domain.
          We do this so you have one less reason to leave when we host the site.
          We don't make money on the name.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div class="section-label">Check availability</div>
        <form @submit.prevent="search" style="display: grid; grid-template-columns: 1fr auto; gap: 1px; background: var(--ink); border: 1px solid var(--ink); max-width: 720px;">
          <input
            v-model="keyword"
            type="text"
            placeholder="yourbusiness"
            style="background: var(--bone); border: none; padding: 1.25rem 1.5rem; font-family: var(--font-mono); font-size: 1rem; color: var(--ink); outline: none;"
            autocomplete="off"
            spellcheck="false"
          />
          <button
            type="submit"
            :disabled="loading || !keyword.trim()"
            class="btn btn-primary"
            style="border: none;"
          >
            {{ loading ? 'Searching…' : 'Check →' }}
          </button>
        </form>

        <p v-if="error" class="micro" style="color: var(--oxblood); margin-top: 1rem;">{{ error }}</p>

        <div v-if="results.length" style="margin-top: 2.5rem;">
          <div class="micro" style="margin-bottom: 1rem;">
            Available for <span style="color: var(--ink);">{{ keyword }}</span>
          </div>
          <table class="table-data">
            <thead>
              <tr>
                <th>Domain</th>
                <th style="text-align: right;">First year</th>
                <th style="text-align: right;">Annual renewal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in results" :key="r.domain">
                <td style="color: var(--ink); font-size: 15px;">{{ r.domain }}</td>
                <td style="text-align: right; color: var(--oxblood); font-size: 15px;">${{ r.first_year.toFixed(2) }}</td>
                <td style="text-align: right;">${{ r.renewal.toFixed(2) }} / yr</td>
              </tr>
            </tbody>
          </table>
          <p class="prose-body" style="font-size: 14px; margin-top: 1rem; font-style: italic;">
            To buy, <Link href="/contact">drop us a line</Link> with the names you want.
            We register and point DNS the same day.
          </p>
        </div>

        <p v-else-if="searched && !loading" class="micro" style="margin-top: 1.5rem;">
          No results came back. Try a different keyword, or see reference pricing below.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div class="section-label">Reference pricing · verified 2026-05-18</div>
        <p class="prose-body" style="margin-bottom: 1.5rem;">
          What you actually pay, by TLD. All prices include the ${{ markup }} markup.
          The wholesale price is shown for transparency.
        </p>

        <div style="overflow-x: auto;">
          <table class="table-data">
            <thead>
              <tr>
                <th>TLD</th>
                <th style="text-align: right;">First year</th>
                <th style="text-align: right;">Annual renewal</th>
                <th style="text-align: right; opacity: 0.6;">Wholesale</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in sortedReference" :key="row.tld">
                <td style="color: var(--ink);">.{{ row.tld }}</td>
                <td style="text-align: right; color: var(--oxblood);">${{ row.first_year.toFixed(2) }}</td>
                <td style="text-align: right;">${{ row.renewal.toFixed(2) }} / yr</td>
                <td style="text-align: right; opacity: 0.6;">${{ row.wholesale_first.toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="section-last">
      <div class="container-wide">
        <h2 class="display-md" style="margin-bottom: 1rem;">Pair it with hosting.</h2>
        <p class="lede" style="max-width: 52ch; margin-bottom: 2rem;">
          Basic hosting includes a free .com for the year.
          If a name on this page is what you want, we set it up at the same time as the droplet.
        </p>
        <Link href="/host" class="btn btn-primary">See hosting →</Link>
      </div>
    </section>
  </Site>
</template>
