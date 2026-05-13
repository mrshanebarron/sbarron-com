<script setup>
// Number ticker that counts up from zero when it enters the viewport.
// Plain text fallback under prefers-reduced-motion.

import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  value: { type: [Number, String], required: true },
  prefix: { type: String, default: '' },
  suffix: { type: String, default: '' },
  duration: { type: Number, default: 1400 },
  format: { type: String, default: 'integer' }, // integer | currency | duration
})

const root = ref(null)
const display = ref(formatVal(0))
let started = false
let raf
let obs

const target = () => Number(String(props.value).replace(/[^\d.]/g, '')) || 0

function formatVal(n) {
  switch (props.format) {
    case 'currency': return props.prefix + Math.round(n).toLocaleString() + props.suffix
    case 'duration': return props.prefix + Math.round(n) + props.suffix
    default:         return props.prefix + Math.round(n).toLocaleString() + props.suffix
  }
}

function ease(t) { return 1 - Math.pow(1 - t, 3) }

function animate() {
  if (started) return
  started = true
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches
  const goal = Number(String(props.value).replace(/[^\d.]/g, '')) || 0
  if (reduced) { display.value = formatVal(goal); return }
  const t0 = performance.now()
  const tick = (now) => {
    const t = Math.min(1, (now - t0) / props.duration)
    display.value = formatVal(goal * ease(t))
    if (t < 1) raf = requestAnimationFrame(tick)
  }
  raf = requestAnimationFrame(tick)
}

onMounted(() => {
  if (!root.value) return
  obs = new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) animate() })
  }, { threshold: 0.35 })
  obs.observe(root.value)
})

onBeforeUnmount(() => {
  if (raf) cancelAnimationFrame(raf)
  if (obs) obs.disconnect()
})
</script>

<template>
  <span ref="root">{{ display }}</span>
</template>
