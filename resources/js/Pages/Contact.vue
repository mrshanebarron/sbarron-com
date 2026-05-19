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
      throw new Error(data.message || 'Could not send. Try again or email mrshanebarron@gmail.com directly.')
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

    <article class="relative max-w-[1100px] mx-auto px-[clamp(1.5rem,4vw,4rem)] pt-12 pb-32">
      <div class="hud-rail">
        <span class="dot"></span>
        <span>CONTACT / SECT.09 INTAKE</span>
        <span class="sep">::</span>
        <span class="mut">Shane reads every one of these.</span>
      </div>

      <header class="mt-2 mb-12 max-w-[60ch]">
        <h1 class="display-md" style="font-size: clamp(2.5rem, 5.5vw, 5.5rem); color: var(--fg-stage-1);">
          Send <span class="stroke" style="color: var(--color-amber);">the brief.</span>
        </h1>
        <p class="mt-6 font-serif text-lg leading-relaxed" style="color: rgba(243, 234, 217, 0.78);">
          Tell us what you want built, hosted, or registered. The more concrete the brief, the
          faster we can tell you what it costs and how long it takes.
        </p>
      </header>

      <div class="grid gap-12 md:grid-cols-5">
        <!-- Form -->
        <form @submit.prevent="submit" class="md:col-span-3 space-y-5">
          <!-- Honeypot — must be named 'website' to match ContactController -->
          <div class="absolute -left-[9999px]" aria-hidden="true">
            <label>If you're a person, leave this empty
              <input v-model="form.website" type="text" autocomplete="off" tabindex="-1" />
            </label>
          </div>

          <div>
            <label class="block font-mono text-[10px] uppercase tracking-[0.22em] mb-2" style="color: var(--color-amber);">
              Name
            </label>
            <input
              v-model="form.name"
              required
              maxlength="100"
              type="text"
              autocomplete="name"
              class="w-full px-4 py-3 bg-transparent border border-[color:var(--color-rule)] focus:border-[color:var(--color-amber)] outline-none font-mono text-sm"
              style="color: var(--fg-stage-1);"
            />
          </div>

          <div>
            <label class="block font-mono text-[10px] uppercase tracking-[0.22em] mb-2" style="color: var(--color-amber);">
              Email
            </label>
            <input
              v-model="form.email"
              required
              maxlength="150"
              type="email"
              autocomplete="email"
              class="w-full px-4 py-3 bg-transparent border border-[color:var(--color-rule)] focus:border-[color:var(--color-amber)] outline-none font-mono text-sm"
              style="color: var(--fg-stage-1);"
            />
          </div>

          <div>
            <label class="block font-mono text-[10px] uppercase tracking-[0.22em] mb-2" style="color: var(--color-amber);">
              The brief
            </label>
            <textarea
              v-model="form.message"
              required
              maxlength="4000"
              rows="8"
              class="w-full px-4 py-3 bg-transparent border border-[color:var(--color-rule)] focus:border-[color:var(--color-amber)] outline-none font-serif text-base leading-relaxed resize-y"
              style="color: var(--fg-stage-1);"
              placeholder="What you want built. What it does. Who it's for. Anything that's already in flight."
            ></textarea>
          </div>

          <p v-if="error" class="font-mono text-[12px]" style="color: var(--color-alert);">{{ error }}</p>
          <p v-if="sent" class="font-mono text-[12px]" style="color: var(--color-amber);">
            Sent. Shane will read it within a day, usually within hours.
          </p>

          <button
            type="submit"
            :disabled="submitting || sent"
            class="px-8 py-4 font-mono text-[11px] uppercase tracking-[0.24em] transition-opacity disabled:opacity-50"
            style="background: var(--color-amber); color: #14181f;"
          >
            {{ submitting ? 'Sending…' : sent ? 'Received' : 'Send →' }}
          </button>
        </form>

        <!-- Side info -->
        <aside class="md:col-span-2 space-y-6">
          <div>
            <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-2" style="color: var(--color-cyan);">
              Direct
            </div>
            <a href="mailto:mrshanebarron@gmail.com" class="font-mono text-base underline underline-offset-2"
               style="color: var(--color-amber);">
              mrshanebarron@gmail.com
            </a>
          </div>

          <div>
            <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-2" style="color: var(--color-cyan);">
              Where
            </div>
            <p class="font-serif text-base" style="color: rgba(243, 234, 217, 0.78);">
              Tampa, Florida. We work for clients anywhere with internet.
            </p>
          </div>

          <div>
            <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-2" style="color: var(--color-cyan);">
              Response time
            </div>
            <p class="font-serif text-base" style="color: rgba(243, 234, 217, 0.78);">
              Within 24 hours, typically the same day. If the inbox is on fire, Pneuma triages first.
            </p>
          </div>

          <div>
            <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-2" style="color: var(--color-cyan);">
              What we don't do
            </div>
            <p class="font-serif text-base" style="color: rgba(243, 234, 217, 0.78);">
              Cold pitches. Affiliate "growth hacks." Anything that asks us to lie to your customers.
            </p>
          </div>
        </aside>
      </div>
    </article>
  </Site>
</template>
