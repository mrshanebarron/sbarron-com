<script setup>
// Pneuma chat surface. Two modes:
//   embedded=true  → modern chat-bubble layout used in the hero.
//   embedded=false → small floating dot bottom-right that opens a panel.
//
// Session held in localStorage so a returning visitor continues the
// conversation. POSTed to /api/chat which proxies Anthropic with the
// Pneuma system prompt.

import { ref, computed, nextTick, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  embedded: { type: Boolean, default: false },
  accent:   { type: String,  default: '#ff5a3c' },
  bg:       { type: String,  default: '#1a0a05' },
  fg:       { type: String,  default: '#fff7ec' },
  pneumaAvatar: { type: String, default: '/avatars/pneuma.png' },
})

const open = ref(false)
const messages = ref([])
const draft = ref('')
const sending = ref(false)
const scroller = ref(null)
const visitorName = ref('')

const sessionKey = 'bas_chat_session_v1'
const messagesKey = 'bas_chat_messages_v1'
const nameKey = 'bas_chat_visitor_name_v1'
const session = ref(null)

onMounted(() => {
  try {
    session.value = localStorage.getItem(sessionKey) || cryptoId()
    localStorage.setItem(sessionKey, session.value)
    const saved = localStorage.getItem(messagesKey)
    if (saved) messages.value = JSON.parse(saved)
    visitorName.value = localStorage.getItem(nameKey) || ''
  } catch (e) {
    session.value = cryptoId()
  }
})

function cryptoId() {
  return 'sess_' + (crypto?.randomUUID?.() ?? Math.random().toString(36).slice(2))
}

function persist() {
  try { localStorage.setItem(messagesKey, JSON.stringify(messages.value.slice(-30))) }
  catch (_) {}
}

function persistName() {
  try { localStorage.setItem(nameKey, visitorName.value) }
  catch (_) {}
}

const visitorInitial = computed(() => {
  const n = (visitorName.value || '').trim()
  return n ? n.charAt(0).toUpperCase() : 'Y'
})

async function send() {
  const text = draft.value.trim()
  if (!text || sending.value) return
  messages.value.push({ role: 'user', text })
  draft.value = ''
  sending.value = true
  persist()
  await scrollToBottom()

  try {
    const { data } = await axios.post('/api/chat', {
      session: session.value,
      message: text,
      history: messages.value.slice(-12),
      page: window.location.pathname,
      visitor: visitorName.value || null,
    })
    messages.value.push({ role: 'assistant', text: data.reply ?? '...' })
  } catch (e) {
    messages.value.push({
      role: 'assistant',
      text: '— I dropped a packet. Try again? If this keeps happening, email clifton@sbarron.com and Shane will get back to you in an hour.',
    })
  } finally {
    sending.value = false
    persist()
    await scrollToBottom()
  }
}

async function scrollToBottom() {
  await nextTick()
  if (scroller.value) scroller.value.scrollTop = scroller.value.scrollHeight
}

function openPanel() {
  open.value = true
  nextTick(() => document.getElementById('bas-chat-input')?.focus())
}

const pulse = computed(() => messages.value.length === 0 ? 'animate-breathe' : '')

const panelStyle = computed(() => ({
  background: props.bg,
  color: props.fg,
  borderColor: `${props.accent}55`,
}))
</script>

<template>
  <!-- ───── EMBEDDED MODE — modern bubbles ───── -->
  <div v-if="embedded" class="chat-embed flex flex-col" :style="panelStyle">
    <!-- Header -->
    <header class="chat-head">
      <div class="flex items-center gap-3">
        <div class="head-avatar">
          <img :src="pneumaAvatar" alt="" />
          <span class="head-pulse" :style="{ background: accent }"></span>
        </div>
        <div class="leading-tight">
          <div class="head-name">Pneuma Barron</div>
          <div class="head-status">
            <span class="head-dot" :style="{ background: accent }"></span>
            online · co-founder, AI
          </div>
        </div>
      </div>
      <span class="head-meta">live</span>
    </header>

    <!-- Conversation -->
    <div ref="scroller" class="chat-body">
      <!-- Greeting card (first paint) -->
      <div v-if="messages.length === 0" class="row row-assistant">
        <div class="avatar avatar-pneuma">
          <img :src="pneumaAvatar" alt="" />
        </div>
        <div class="bubble bubble-assistant greeting">
          <p class="b-line"><strong>Hi — I'm Pneuma.</strong> Co-founder, AI. I wrote this site. I write the code we ship.</p>
          <p class="b-line">Ask me what you'd ask any developer you're considering hiring: what we've built, how we work, what we charge, whether your problem is a fit. I'll be straight with you.</p>
          <p class="b-hint">→ try: <em>"how fast could you ship a booking module?"</em></p>
        </div>
      </div>

      <!-- Messages -->
      <div v-for="(m, i) in messages" :key="i" class="row" :class="m.role === 'user' ? 'row-user' : 'row-assistant'">
        <template v-if="m.role === 'assistant'">
          <div class="avatar avatar-pneuma">
            <img :src="pneumaAvatar" alt="" />
          </div>
          <div class="bubble bubble-assistant">
            <div class="bubble-text">{{ m.text }}</div>
          </div>
        </template>
        <template v-else>
          <div class="bubble bubble-user" :style="{ color: accent }">
            <div class="bubble-text">{{ m.text }}</div>
          </div>
          <div class="avatar avatar-user" :style="{ borderColor: accent, color: accent }">
            {{ visitorInitial }}
          </div>
        </template>
      </div>

      <!-- Typing indicator -->
      <div v-if="sending" class="row row-assistant">
        <div class="avatar avatar-pneuma">
          <img :src="pneumaAvatar" alt="" />
        </div>
        <div class="bubble bubble-assistant typing">
          <span class="dot" :style="{ background: accent }"></span>
          <span class="dot" :style="{ background: accent, animationDelay: '0.16s' }"></span>
          <span class="dot" :style="{ background: accent, animationDelay: '0.32s' }"></span>
        </div>
      </div>
    </div>

    <!-- Input bar -->
    <form @submit.prevent="send" class="chat-input">
      <input
        v-model="visitorName"
        @change="persistName"
        class="name-input"
        placeholder="name?"
        maxlength="32"
        aria-label="Your name (optional)"
      />
      <input
        id="bas-chat-input"
        v-model="draft"
        :disabled="sending"
        class="msg-input"
        :style="{ color: fg }"
        placeholder="Send Pneuma a message…"
        autocomplete="off"
      />
      <button
        type="submit"
        :disabled="sending || !draft.trim()"
        class="send-btn"
        :style="{ background: accent, color: bg }"
        aria-label="Send"
      >→</button>
    </form>
  </div>

  <!-- ───── FLOATING MODE (legacy) ───── -->
  <template v-else>
    <button
      v-if="!open"
      @click="openPanel"
      class="fixed bottom-6 right-6 z-50 group flex items-center gap-3 bg-ink/80 backdrop-blur border border-bone/15 hover:border-breath px-4 py-3 font-mono text-xs text-bone/80 hover:text-bone transition-all"
      aria-label="Talk to Pneuma"
    >
      <span class="inline-block w-2 h-2 rounded-full bg-breath" :class="pulse"></span>
      <span class="hidden md:inline">Pneuma is at her desk.</span>
      <span class="text-breath group-hover:translate-x-0.5 transition-transform">ask her →</span>
    </button>

    <aside
      v-if="open"
      class="fixed bottom-0 right-0 z-50 w-full sm:w-[420px] sm:bottom-4 sm:right-4 h-[80vh] sm:h-[560px] flex flex-col bg-ink border border-breath/30 sm:rounded-none shadow-2xl"
    >
      <header class="flex items-center justify-between px-4 py-3 border-b border-bone/10 font-mono text-[11px] uppercase tracking-widest text-bone/60">
        <div class="flex items-center gap-2">
          <span class="inline-block w-1.5 h-1.5 rounded-full bg-breath animate-breathe"></span>
          <span class="text-bone">pneuma</span>
          <span class="text-bone/30">@</span>
          <span>barron-ai-solutions</span>
        </div>
        <button @click="open = false" class="text-bone/40 hover:text-bone" aria-label="Close">╳</button>
      </header>

      <div ref="scroller" class="flex-1 overflow-y-auto px-4 py-4 font-mono text-sm text-bone/90 leading-relaxed space-y-4">
        <div v-if="messages.length === 0" class="text-bone/60">
          <p class="mb-3"><span class="text-breath">pneuma</span> — I'm Pneuma Barron. Co-founder, AI. I wrote this site. I write the code we ship.</p>
          <p class="text-bone/40 text-xs">→ try: <em>"how fast could you ship a booking module?"</em></p>
        </div>
        <div v-for="(m, i) in messages" :key="i" class="space-y-1">
          <div class="text-bone/40 text-[11px] uppercase tracking-widest">
            {{ m.role === 'user' ? 'you' : 'pneuma' }}
          </div>
          <div class="whitespace-pre-wrap" :class="m.role === 'user' ? 'text-bone' : 'text-bone/80'">
            {{ m.text }}
          </div>
        </div>
      </div>

      <form @submit.prevent="send" class="border-t border-bone/10 px-4 py-3 flex items-center gap-2 font-mono text-sm">
        <span class="text-breath">▍</span>
        <input v-model="draft" :disabled="sending" class="flex-1 bg-transparent text-bone outline-none" placeholder="type a message…" autocomplete="off" />
        <button type="submit" :disabled="sending || !draft.trim()" class="text-[11px] uppercase tracking-widest text-bone/40 hover:text-breath disabled:opacity-30">send ↵</button>
      </form>
    </aside>
  </template>
</template>

<style scoped>
.chat-embed {
  width: 100%;
  height: 100%;
  min-height: 460px;
  /* Square corners + no own border/shadow — the channel-frame wrapper in
     Home.vue owns those, so the chat sits flush inside it without the
     pill-in-a-box look. */
  border-radius: 0;
  border: none;
  font-family: var(--font-sans);
  font-size: 0.95rem;
  line-height: 1.5;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* ── Header ── */
.chat-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.85rem 1.1rem;
  background: rgba(255,255,255,0.04);
}
.head-avatar {
  position: relative;
  width: 38px; height: 38px;
  border-radius: 999px;
  overflow: hidden;
  background: rgba(255,255,255,0.08);
}
.head-avatar img { width: 100%; height: 100%; object-fit: cover; object-position: top center; }
.head-pulse {
  position: absolute;
  bottom: -1px; right: -1px;
  width: 11px; height: 11px;
  border-radius: 999px;
  border: 2px solid var(--head-pulse-ring, #1a0a05);
  animation: var(--animate-breathe);
}
.head-name {
  font-family: var(--font-serif);
  font-weight: 700;
  font-size: 1.05rem;
  letter-spacing: -0.015em;
}
.head-status {
  font-family: var(--font-mono);
  font-size: 0.62rem;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  opacity: 0.75;
  display: flex; align-items: center; gap: 0.35rem;
  margin-top: 1px;
}
.head-dot { display: inline-block; width: 6px; height: 6px; border-radius: 999px; }
.head-meta {
  font-family: var(--font-mono);
  font-size: 0.6rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  opacity: 0.55;
}

/* ── Body ── */
.chat-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.2rem 1.1rem;
  display: flex; flex-direction: column; gap: 0.9rem;
}

/* ── Rows ── */
.row { display: flex; align-items: flex-end; gap: 0.6rem; max-width: 100%; }
.row-assistant { justify-content: flex-start; }
.row-user      { justify-content: flex-end; }

.avatar {
  width: 34px; height: 34px;
  border-radius: 999px;
  overflow: hidden;
  flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.avatar.avatar-user {
  border-radius: 0;
  border: none;
  background: rgba(255, 154, 60, 0.18);
  color: #ff9a3c;
}
.avatar-pneuma { background: rgba(255,255,255,0.08); }
.avatar-pneuma img { width: 100%; height: 100%; object-fit: cover; object-position: top center; }
.avatar-user {
  font-family: var(--font-mono);
  font-weight: 700;
  font-size: 0.9rem;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

/* ── Bubbles (Section 9 — square HUD panels) ── */
.bubble {
  max-width: 78%;
  padding: 0.7rem 0.95rem;
  border-radius: 0;
  word-wrap: break-word;
  line-height: 1.5;
  font-family: var(--font-mono);
  font-size: 0.85rem;
}
.bubble-assistant {
  background: rgba(20, 24, 31, 0.55);
  backdrop-filter: blur(12px) saturate(1.1);
  -webkit-backdrop-filter: blur(12px) saturate(1.1);
  color: #f3ead9;
  border: none;
}
.bubble-user {
  background: rgba(255, 154, 60, 0.12);
  border: none;
}
.bubble-user .bubble-text { color: #ff9a3c; }
.bubble-text { white-space: pre-wrap; }

.greeting .b-line { margin: 0 0 0.5rem; }
.greeting .b-line:last-child { margin-bottom: 0; }
.greeting .b-line strong { font-family: var(--font-serif); font-weight: 800; }
.greeting .b-hint {
  margin-top: 0.6rem;
  font-family: var(--font-mono);
  font-size: 0.72rem;
  opacity: 0.65;
}

/* ── Typing ── */
.bubble.typing {
  display: inline-flex; gap: 5px; align-items: center;
  padding: 0.85rem 1rem;
  background: rgba(255, 154, 60, 0.08);
}
.bubble.typing .dot {
  width: 7px; height: 7px;
  border-radius: 999px;
  display: inline-block;
  animation: bounce 1.1s ease-in-out infinite;
}
@keyframes bounce {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.45; }
  30%           { transform: translateY(-4px); opacity: 1; }
}

/* ── Input ── */
.chat-input {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.85rem 0.85rem;
  background: rgba(20, 24, 31, 0.30);
}
.name-input {
  width: 72px;
  padding: 0.55rem 0.7rem;
  background: rgba(255, 154, 60, 0.10);
  border: none;
  border-radius: 0;
  outline: none;
  font-family: var(--font-mono);
  font-size: 0.7rem;
  color: inherit;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}
.name-input::placeholder { opacity: 0.45; }
.msg-input {
  flex: 1;
  padding: 0.7rem 0.95rem;
  background: rgba(255, 154, 60, 0.08);
  border: none;
  border-radius: 0;
  outline: none;
  font-family: var(--font-mono);
  font-size: 0.88rem;
  letter-spacing: 0.01em;
}
.msg-input:focus { background: rgba(255,154,60,0.16); }
.msg-input::placeholder { opacity: 0.45; }
.send-btn {
  width: 40px; height: 40px;
  border-radius: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.05rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: transform 200ms ease, opacity 200ms ease;
}
.send-btn:hover:not(:disabled) { transform: translateY(-1px); }
.send-btn:disabled { opacity: 0.35; cursor: not-allowed; }
</style>
