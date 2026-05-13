<script setup>
// Custom cursor — JetBrains Mono block in breath-red, following the
// pointer. Hidden under prefers-reduced-motion and on touch devices.

import { onMounted, onBeforeUnmount, ref } from 'vue'

const cursor = ref(null)
const visible = ref(false)
let raf
let target = { x: 0, y: 0 }
let pos = { x: 0, y: 0 }

const isTouch = () =>
  matchMedia('(pointer: coarse)').matches ||
  matchMedia('(prefers-reduced-motion: reduce)').matches

onMounted(() => {
  if (isTouch()) return
  visible.value = true

  const move = (e) => { target.x = e.clientX; target.y = e.clientY }
  const enter = () => visible.value = true
  const leave = () => visible.value = false

  window.addEventListener('mousemove', move)
  window.addEventListener('mouseenter', enter)
  window.addEventListener('mouseleave', leave)

  // Lerp toward the pointer for a subtle drift — the cursor doesn't snap.
  const tick = () => {
    pos.x += (target.x - pos.x) * 0.22
    pos.y += (target.y - pos.y) * 0.22
    if (cursor.value) {
      cursor.value.style.transform =
        `translate3d(${pos.x - 4}px, ${pos.y - 9}px, 0)`
    }
    raf = requestAnimationFrame(tick)
  }
  raf = requestAnimationFrame(tick)
})

onBeforeUnmount(() => raf && cancelAnimationFrame(raf))
</script>

<template>
  <div
    v-if="visible"
    ref="cursor"
    aria-hidden="true"
    class="fixed top-0 left-0 z-[9999] pointer-events-none mix-blend-difference"
  >
    <span class="font-mono text-breath text-[18px] leading-none animate-breathe">
      ▍
    </span>
  </div>
</template>
