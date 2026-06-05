<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'
import axios from 'axios'
import Site from '@/Layouts/Site.vue'

const props = defineProps({
  doc: { type: Object, required: true },
  html: { type: String, required: true },
  comments: { type: Array, default: () => [] },
})

// Local copy so a just-approved list could update without a full reload; the
// server is the source of truth on next navigation.
const shown = ref([...props.comments])

const form = reactive({
  author_name: '',
  author_email: '',
  body: '',
  website: '', // honeypot — real users never fill this
})
const state = reactive({ sending: false, message: '', error: '' })

async function submit() {
  state.error = ''
  state.message = ''
  if (!form.author_name.trim() || form.body.trim().length < 2) {
    state.error = 'A name and a comment are required.'
    return
  }
  state.sending = true
  try {
    // axios auto-sends Laravel's XSRF-TOKEN cookie as X-XSRF-TOKEN (same as
    // the working ContactForm), so the session CSRF check passes without a
    // meta tag.
    const { data } = await axios.post('/api/vision/comments', {
      doc_slug: props.doc.slug,
      author_name: form.author_name,
      author_email: form.author_email,
      body: form.body,
      website: form.website,
    })
    if (data.ok) {
      state.message = data.message || 'Thanks — your comment is held for review.'
      form.author_name = ''
      form.author_email = ''
      form.body = ''
    } else {
      state.error = data.error || 'Something went wrong. Try again in a moment.'
    }
  } catch (e) {
    state.error = e?.response?.data?.error || 'Network error — please try again.'
  } finally {
    state.sending = false
  }
}
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

        <!-- Approved comments. Bodies are rendered as plain text ({{ }}),
             never v-html, so submitted markup cannot inject. -->
        <div v-if="shown.length" style="margin-top: 1.5rem;">
          <div
            v-for="(c, i) in shown"
            :key="i"
            style="padding: 1.25rem 0; border-bottom: 1px solid var(--ink-soft);"
          >
            <div class="micro" style="margin-bottom: 0.5rem;">
              <span style="color: var(--bone);">{{ c.author }}</span>
              <span style="color: var(--ink-faint);"> · {{ c.date }}</span>
            </div>
            <p class="prose-body" style="white-space: pre-wrap;">{{ c.body }}</p>
          </div>
        </div>
        <p v-else class="prose-body" style="margin-top: 1rem; color: var(--ink-mute);">
          No comments yet. Be the first — and you can always reply in the open on
          <a href="https://x.com/visionoverflow" target="_blank" rel="noopener" style="color: var(--oxblood);">@visionoverflow</a>.
        </p>

        <!-- Submit form. Comments are held for review before they appear. -->
        <form @submit.prevent="submit" style="margin-top: 2.5rem; max-width: 48rem;">
          <div class="micro micro-accent" style="margin-bottom: 1rem;">Add a comment</div>

          <!-- honeypot: visually hidden, bots fill it, humans don't -->
          <input
            v-model="form.website"
            type="text"
            name="website"
            tabindex="-1"
            autocomplete="off"
            aria-hidden="true"
            style="position:absolute; left:-9999px; width:1px; height:1px; opacity:0;"
          />

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <input
              v-model="form.author_name"
              type="text"
              placeholder="Name"
              maxlength="80"
              required
              style="padding: 0.7rem 0.9rem; border: 1px solid var(--ink); background: transparent; color: var(--bone); font: inherit;"
            />
            <input
              v-model="form.author_email"
              type="email"
              placeholder="Email (optional, never shown)"
              maxlength="200"
              style="padding: 0.7rem 0.9rem; border: 1px solid var(--ink); background: transparent; color: var(--bone); font: inherit;"
            />
          </div>
          <textarea
            v-model="form.body"
            placeholder="Your comment — questions, pushback, or ask for the schema behind a number."
            rows="4"
            maxlength="4000"
            required
            style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid var(--ink); background: transparent; color: var(--bone); font: inherit; resize: vertical;"
          ></textarea>

          <div style="margin-top: 1rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <button
              type="submit"
              :disabled="state.sending"
              class="btn btn-secondary"
            >{{ state.sending ? 'Sending…' : 'Post comment' }}</button>
            <span class="micro" style="color: var(--ink-faint);">Held for review before it appears.</span>
          </div>

          <p v-if="state.message" class="micro" style="margin-top: 1rem; color: var(--bone);">{{ state.message }}</p>
          <p v-if="state.error" class="micro" style="margin-top: 1rem; color: var(--oxblood);">{{ state.error }}</p>
        </form>
      </section>
    </article>
  </Site>
</template>
