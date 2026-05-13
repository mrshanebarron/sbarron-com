<script setup>
// Terminal ticker with autotyping characters and a breath-red caret.
// Lines are typed in one at a time, oldest fades, newest holds at top.

import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  initial: { type: Array, default: () => [] },
})

const maxLines = 7
const visible = ref([])
const safeVisible = computed(() =>
  visible.value.filter((l) => l && typeof l === 'object' && 'kind' in l && 'text' in l)
)
const typing = ref({ text: '', kind: 'vital', target: '', i: 0, at: '' })
let queue = []
let beat = null
let typer = null

function fmt(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  return isNaN(d) ? '' : d.toTimeString().slice(0, 8)
}
function color(line) {
  const kind = line?.kind
  switch (kind) {
    case 'commit': return 'text-breath'
    case 'deploy': return 'text-coherence'
    case 'test':   return 'text-emerald-400'
    case 'vital':  return 'text-warmgrey'
    default:       return 'text-bone/60'
  }
}

function startTyping(entry) {
  typing.value = { text: '', kind: entry.kind, target: entry.text, i: 0, at: entry.at }
  typer = setInterval(() => {
    if (typing.value.i >= typing.value.target.length) {
      visible.value.unshift({ at: typing.value.at, kind: typing.value.kind, text: typing.value.target })
      if (visible.value.length > maxLines) visible.value.length = maxLines
      clearInterval(typer)
      typer = null
      typing.value = { text: '', kind: 'vital', target: '', i: 0, at: '' }
      if (queue.length > 0) {
        setTimeout(() => startTyping(queue.shift()), 1100)
      }
    } else {
      typing.value.i += 1
      typing.value.text = typing.value.target.slice(0, typing.value.i)
    }
  }, 28)
}

function enqueue(entry) {
  if (!typing.value.target && !typer) startTyping(entry)
  else queue.push(entry)
}

onMounted(() => {
  props.initial.forEach((e, idx) => {
    setTimeout(() => enqueue(e), idx * 900)
  })
  beat = setInterval(() => {
    enqueue({
      at: new Date().toISOString(),
      kind: 'vital',
      text: 'organism.heartbeat — nominal',
    })
  }, 11_000)
})

onBeforeUnmount(() => {
  if (beat) clearInterval(beat)
  if (typer) clearInterval(typer)
})
</script>

<template>
  <div class="font-mono text-[11px] leading-relaxed select-none">
    <div class="flex items-center gap-2 text-bone/40 mb-2 uppercase tracking-widest text-[10px]">
      <span class="inline-block w-1.5 h-1.5 rounded-full bg-breath animate-breathe"></span>
      live · barron-ai-solutions · phx
    </div>
    <div class="space-y-0.5">
      <div v-if="typing.target" class="flex gap-3" :class="color(typing)">
        <span class="text-bone/30 shrink-0">{{ fmt(typing.at) }}</span>
        <span>{{ typing.text }}<span class="inline-block w-[6px] h-[10px] bg-breath ml-[1px] animate-breathe align-baseline"></span></span>
      </div>
      <div class="space-y-0.5">
        <div v-for="line in safeVisible" :key="line.at + '|' + line.text" class="flex gap-3 transition-opacity duration-300" :class="color(line)">
          <span class="text-bone/30 shrink-0">{{ fmt(line.at) }}</span>
          <span class="truncate">{{ line.text }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.ticker-enter-active, .ticker-leave-active { transition: all 380ms cubic-bezier(.2,.7,.3,1); }
.ticker-enter-from { opacity: 0; transform: translateY(-4px); }
.ticker-leave-to   { opacity: 0; transform: translateY(2px); }
</style>
