<script setup>
// Button that physically attracts toward the cursor when nearby.
// Used as a wrapper around any anchor or button child.

import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  pull: { type: Number, default: 0.4 },     // strength: 0..1
  radius: { type: Number, default: 180 },   // px from center where pull starts
})

const root = ref(null)
const inner = ref(null)
let raf, current = { x: 0, y: 0 }, target = { x: 0, y: 0 }
const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches ||
                     matchMedia('(pointer: coarse)').matches

function onMove(e) {
  if (!root.value || reduced()) return
  const rect = root.value.getBoundingClientRect()
  const cx = rect.left + rect.width / 2
  const cy = rect.top + rect.height / 2
  const dx = e.clientX - cx
  const dy = e.clientY - cy
  const dist = Math.hypot(dx, dy)
  if (dist > props.radius) {
    target.x = 0; target.y = 0
  } else {
    const k = (1 - dist / props.radius) * props.pull
    target.x = dx * k
    target.y = dy * k
  }
}

function tick() {
  current.x += (target.x - current.x) * 0.18
  current.y += (target.y - current.y) * 0.18
  if (inner.value) {
    inner.value.style.transform = `translate3d(${current.x}px, ${current.y}px, 0)`
  }
  raf = requestAnimationFrame(tick)
}

onMounted(() => {
  if (reduced()) return
  window.addEventListener('mousemove', onMove)
  raf = requestAnimationFrame(tick)
})
onBeforeUnmount(() => {
  if (raf) cancelAnimationFrame(raf)
  window.removeEventListener('mousemove', onMove)
})
</script>

<template>
  <div ref="root" class="inline-block">
    <div ref="inner" class="inline-block transition-transform duration-150 ease-out will-change-transform">
      <slot />
    </div>
  </div>
</template>
