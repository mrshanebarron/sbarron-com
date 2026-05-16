<script setup>
/**
 * Engage / contact form. POSTs to /api/contact, which persists the row
 * and mails mrshanebarron@gmail.com. Inline success/error UI, no page
 * reload. Honeypot via hidden `website` input — bots fill it, real
 * visitors can't see it.
 *
 * Styling follows the Section 9 chrome: borderless glassmorphic inputs
 * with amber focus rings, mono uppercase chrome.
 */
import { ref } from 'vue'
import axios from 'axios'

const name = ref('')
const email = ref('')
const subject = ref('')
const message = ref('')
const website = ref('')  // honeypot

const sending = ref(false)
const sent = ref(false)
const error = ref('')

async function submit() {
  if (sending.value) return
  if (!name.value.trim() || !email.value.trim() || !message.value.trim()) {
    error.value = 'name, email, and message are all required.'
    return
  }
  error.value = ''
  sending.value = true
  try {
    const { data } = await axios.post('/api/contact', {
      name: name.value.trim(),
      email: email.value.trim(),
      subject: subject.value.trim() || null,
      message: message.value.trim(),
      website: website.value,
      page: window.location.pathname,
    })
    if (data?.ok) {
      sent.value = true
      name.value = email.value = subject.value = message.value = ''
    } else {
      error.value = data?.error || 'something went sideways — try again?'
    }
  } catch (e) {
    if (e?.response?.status === 422) {
      error.value = 'check that name, email, and message look right.'
    } else if (e?.response?.status === 429) {
      error.value = e.response.data?.error || "you've sent a few — give it a minute."
    } else {
      error.value = 'we dropped a packet. try again, or email mrshanebarron@gmail.com directly.'
    }
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <form @submit.prevent="submit" class="contact-form" novalidate>
    <!-- Honeypot — hidden from real users. Bots fill it; we drop their sub silently. -->
    <div class="hp" aria-hidden="true">
      <label>website (leave empty)</label>
      <input v-model="website" type="text" tabindex="-1" autocomplete="off" />
    </div>

    <div v-if="sent" class="sent-card">
      <div class="sent-head">// MESSAGE RELAYED</div>
      <p>Shane has it. He'll reply within the day. Usually faster.</p>
    </div>

    <template v-else>
      <div class="row">
        <div class="field">
          <label>NAME</label>
          <input v-model="name" type="text" required maxlength="120" />
        </div>
        <div class="field">
          <label>EMAIL</label>
          <input v-model="email" type="email" required maxlength="200" />
        </div>
      </div>

      <div class="field">
        <label>SUBJECT <span class="opt">// optional</span></label>
        <input v-model="subject" type="text" maxlength="200" placeholder="booking module, AI integration, fix something broken…" />
      </div>

      <div class="field">
        <label>MESSAGE</label>
        <textarea v-model="message" required maxlength="5000" rows="5" placeholder="What are you trying to build, fix, or grow?"></textarea>
      </div>

      <div v-if="error" class="error">// {{ error }}</div>

      <button type="submit" :disabled="sending" class="send-btn">
        <span>{{ sending ? 'SENDING…' : 'SEND TO SHANE' }}</span>
        <span>→</span>
      </button>
    </template>
  </form>
</template>

<style scoped>
.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 560px;
}

.hp {
  position: absolute;
  left: -9999px;
  width: 1px;
  height: 1px;
  overflow: hidden;
}

.row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
@media (max-width: 540px) {
  .row { grid-template-columns: 1fr; }
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.field label {
  font-family: var(--font-mono);
  font-size: 0.62rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.22em;
  color: var(--color-amber);
}
.field label .opt {
  color: rgba(243, 234, 217, 0.5);
  font-weight: 400;
  margin-left: 0.4em;
}

.field input,
.field textarea {
  background: rgba(20, 24, 31, 0.55);
  backdrop-filter: blur(10px) saturate(1.1);
  -webkit-backdrop-filter: blur(10px) saturate(1.1);
  color: var(--fg-stage-7);
  font-family: var(--font-mono);
  font-size: 0.9rem;
  letter-spacing: 0.01em;
  padding: 0.7rem 0.9rem;
  border: none;
  border-radius: 0;
  outline: none;
  transition: background 200ms ease, box-shadow 200ms ease;
  resize: vertical;
}
.field input::placeholder,
.field textarea::placeholder {
  color: rgba(243, 234, 217, 0.35);
}
.field input:focus,
.field textarea:focus {
  background: rgba(255, 154, 60, 0.10);
  box-shadow: 0 0 0 1px rgba(255, 154, 60, 0.55);
}

.error {
  font-family: var(--font-mono);
  font-size: 0.72rem;
  letter-spacing: 0.14em;
  color: #ff6868;
  text-transform: uppercase;
}

.send-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.8rem;
  padding: 1rem 1.6rem;
  background: var(--color-amber);
  color: #14181f;
  border: none;
  border-radius: 999px;
  font-family: var(--font-mono);
  font-size: 0.9rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  cursor: pointer;
  transition: transform 200ms ease, opacity 200ms ease;
  align-self: flex-start;
}
.send-btn:hover { transform: translateY(-2px); }
.send-btn:disabled { opacity: 0.5; cursor: wait; transform: none; }

.sent-card {
  padding: 1.5rem 1.4rem;
  background: rgba(255, 154, 60, 0.10);
  backdrop-filter: blur(10px) saturate(1.1);
  -webkit-backdrop-filter: blur(10px) saturate(1.1);
  color: var(--fg-stage-7);
}
.sent-head {
  font-family: var(--font-mono);
  font-size: 0.7rem;
  letter-spacing: 0.24em;
  color: var(--color-amber);
  margin-bottom: 0.5rem;
}
.sent-card p {
  font-family: var(--font-serif);
  font-size: 1.1rem;
  margin: 0;
}
</style>
