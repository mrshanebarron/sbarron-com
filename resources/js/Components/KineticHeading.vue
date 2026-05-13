<script setup>
// 3D letter-by-letter reveal. Each character starts rotated, blurred,
// and translated down; lands in sequence with a stagger. Uses GSAP
// SplitText-style manual splitting (no GSAP plugin license needed).

import { ref, onMounted, onBeforeUnmount } from 'vue'
import { gsap } from 'gsap'

const props = defineProps({
  text: { type: String, required: true },
  delay: { type: Number, default: 0 },
  stagger: { type: Number, default: 0.04 },
  duration: { type: Number, default: 1.1 },
})

const root = ref(null)
let tween

function splitToSpans(text) {
  // Preserve spaces but wrap each character; <br> stays as a hard break.
  return text.split('').map((ch) => {
    if (ch === ' ') return '<span class="inline-block">&nbsp;</span>'
    return `<span class="inline-block will-change-transform" style="transform-style:preserve-3d;">${ch}</span>`
  }).join('')
}

onMounted(() => {
  if (!root.value) return
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches
  if (reduced) return

  const chars = root.value.querySelectorAll('span > span')
  gsap.set(chars, {
    opacity: 0,
    rotateX: -90,
    y: 30,
    filter: 'blur(8px)',
    transformOrigin: '50% 100% -20px',
  })
  tween = gsap.to(chars, {
    opacity: 1,
    rotateX: 0,
    y: 0,
    filter: 'blur(0px)',
    duration: props.duration,
    stagger: props.stagger,
    ease: 'power3.out',
    delay: props.delay,
  })
})

onBeforeUnmount(() => { if (tween) tween.kill() })
</script>

<template>
  <span ref="root">
    <!-- v-html is safe here: parent passes literal text only -->
    <span v-html="splitToSpans(text)"></span>
  </span>
</template>
