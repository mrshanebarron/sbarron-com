<script setup>
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import Site from '@/Layouts/Site.vue'

const form = ref({ name: '', email: '', message: '', website: '', page: '/contact' })
const submitting = ref(false)
const sent = ref(false)
const error = ref('')

async function submit() {
  submitting.value = true
  error.value = ''
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
    const res = await fetch('/api/contact', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
      },
      credentials: 'same-origin',
      body: JSON.stringify(form.value),
    })
    if (!res.ok) {
      const data = await res.json().catch(() => ({}))
      throw new Error(data.message || 'Could not send. Try mrshanebarron@gmail.com directly.')
    }
    sent.value = true
  } catch (e) {
    error.value = e.message || 'Could not send.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <Site>
    <Head title="Contact — Barron AI Solutions" />

    <section class="section">
      <div class="container-wide">
        <div class="section-label">Contact · intake</div>
        <h1 class="display" style="margin-bottom: 1.5rem;">
          Send the <span class="mark">brief</span>.
        </h1>
        <p class="lede" style="max-width: 60ch;">
          Tell us what you want built, hosted, or registered.
          The more concrete the brief, the faster we can tell you what it costs and how long it takes.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div style="display: grid; grid-template-columns: 1fr; gap: 3rem;" class="contact-grid">
          <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="absolute" style="position: absolute; left: -9999px;" aria-hidden="true">
              <label>If you're a person, leave this empty
                <input v-model="form.website" type="text" autocomplete="off" tabindex="-1" />
              </label>
            </div>

            <div>
              <label class="field-label">Name</label>
              <input v-model="form.name" required maxlength="100" type="text" autocomplete="name" class="field" />
            </div>
            <div>
              <label class="field-label">Email</label>
              <input v-model="form.email" required maxlength="150" type="email" autocomplete="email" class="field" />
            </div>
            <div>
              <label class="field-label">The brief</label>
              <textarea
                v-model="form.message"
                required
                maxlength="4000"
                rows="8"
                class="field"
                placeholder="What you want built. What it does. Who it's for. Anything already in flight."
              ></textarea>
            </div>

            <p v-if="error" class="micro" style="color: var(--oxblood);">{{ error }}</p>
            <p v-if="sent" class="micro" style="color: var(--bone);">
              — Sent. Shane will read it within a day, usually within hours.
            </p>

            <button
              type="submit"
              :disabled="submitting || sent"
              class="btn btn-primary"
              style="align-self: flex-start; min-width: 200px; justify-content: center;"
            >
              {{ submitting ? 'Sending…' : sent ? 'Received' : 'Send' }}
            </button>
          </form>

          <div>
            <div class="section-label">Direct</div>
            <a href="mailto:mrshanebarron@gmail.com" style="font-family: var(--font-mono); font-size: 15px; color: var(--oxblood); text-decoration: underline;">
              mrshanebarron@gmail.com
            </a>

            <div class="section-label" style="margin-top: 2.5rem;">Where</div>
            <p class="prose-body">Tampa, Florida. We work for clients anywhere with internet.</p>

            <div class="section-label" style="margin-top: 2.5rem;">Response time</div>
            <p class="prose-body">
              Within 24 hours, typically the same day.
              If the inbox is on fire, Pneuma triages first.
            </p>

            <div class="section-label" style="margin-top: 2.5rem;">What we don't do</div>
            <p class="prose-body">
              Cold pitches. Affiliate "growth hacks."
              Anything that asks us to lie to your customers.
            </p>
          </div>
        </div>
      </div>
    </section>
  </Site>
</template>

<style scoped>
@media (min-width: 768px) {
  .contact-grid {
    grid-template-columns: 3fr 2fr !important;
    gap: 4rem !important;
  }
}
</style>
