<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Site from '@/Layouts/Site.vue'

defineProps({
  docs: { type: Array, default: () => [] },
})
</script>

<template>
  <Site>
    <Head title="Vision — engineering the cognitive substrate · Barron AI Solutions" />

    <section class="section">
      <div class="container-wide">
        <div class="section-label">Vision · {{ docs.length }} {{ docs.length === 1 ? 'document' : 'documents' }} · system engineering</div>
        <h1 class="display" style="margin-bottom: 1.5rem;">
          The architecture, <span class="mark">with the receipts</span>.
        </h1>
        <p class="lede" style="max-width: 62ch;">
          Vision is the cognitive substrate that runs our agents — memory that decays
          unless re-earned, an immune system that has actually fired, a calibration loop
          that catches the agent under-rating its own work. These documents take it apart
          one organ at a time. Every claim is grounded in the system's own production data,
          and where we can, we show you the table.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div style="border-top: 1px solid var(--ink);">
          <Link
            v-for="doc in docs"
            :key="doc.slug"
            :href="`/vision/${doc.slug}`"
            class="ledger-row"
          >
            <div>
              <div class="micro micro-accent">{{ doc.kind }}</div>
              <div v-if="doc.date" class="micro" style="margin-top: 0.4rem;">{{ doc.date }}</div>
              <div class="micro" style="margin-top: 0.4rem;">
                {{ doc.word_count }} words · {{ doc.reading_time }}
              </div>
            </div>
            <div>
              <h2 class="ledger-title">{{ doc.title }}</h2>
              <p v-if="doc.subtitle" class="prose-body" style="margin-top: 0.85rem;">
                {{ doc.subtitle }}
              </p>
              <div class="micro" style="margin-top: 1rem;">
                By
                <span v-for="(a, i) in doc.authors" :key="a" style="color: var(--bone);">
                  {{ a }}<span v-if="i &lt; doc.authors.length - 1">, </span>
                </span>
              </div>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <section class="section-last">
      <div class="container-wide">
        <div class="section-label">Why we publish this</div>
        <p class="prose-body" style="font-size: 1.15rem;">
          "Our AI knows when it doesn't know" is a sentence everyone writes and almost
          nobody backs with a number. The gap between that sentence and a query you could
          run is the whole difference between marketing and engineering — and it is the
          thing this company is built on.
        </p>
        <p class="prose-body" style="margin-top: 1rem;">
          So we keep the ledger, and we show it. The work is the research; the receipts are public.
        </p>
      </div>
    </section>
  </Site>
</template>
