// useReveal — GSAP ScrollTrigger primitives for the four-movement funnel.
// Public surface is small on purpose: revealOnEnter, highlightOnEnter,
// wipeBetween. Heavy plugin loading lives here so pages stay clean.

import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

// Returns a cleanup fn — caller passes it to onBeforeUnmount.
export function revealOnEnter(targetSelector, opts = {}) {
  const els = gsap.utils.toArray(targetSelector)
  const triggers = els.map((el) => {
    gsap.set(el, { opacity: 0, y: 24, filter: 'blur(8px)' })
    return ScrollTrigger.create({
      trigger: el,
      start: opts.start ?? 'top 82%',
      once: true,
      onEnter: () => {
        gsap.to(el, {
          opacity: 1,
          y: 0,
          filter: 'blur(0px)',
          duration: 0.9,
          ease: 'power3.out',
          delay: opts.delay ?? 0,
        })
      },
    })
  })
  return () => triggers.forEach((t) => t.kill())
}

// Marker-pen sweep. A pseudo-element with a colored backdrop scales x 0→1
// as the target enters view. Color is passed in CSS-var-friendly hex.
export function highlightOnEnter(targetSelector, color = '#e8443b') {
  const els = gsap.utils.toArray(targetSelector)
  const triggers = els.map((el) => {
    el.style.setProperty('--hl-color', color)
    el.classList.add('marker-highlight')
    gsap.set(el, { '--hl-scale': 0 })
    return ScrollTrigger.create({
      trigger: el,
      start: 'top 80%',
      once: true,
      onEnter: () => {
        gsap.to(el, { '--hl-scale': 1, duration: 0.85, ease: 'power2.inOut', delay: 0.2 })
      },
    })
  })
  return () => triggers.forEach((t) => t.kill())
}

// Clip-path wipe between section transitions. Pass the section selector
// and a direction. Section enters by revealing itself under a wipe.
export function wipeIn(targetSelector, direction = 'horizontal') {
  const els = gsap.utils.toArray(targetSelector)
  const initial = direction === 'horizontal'
    ? 'inset(0% 100% 0% 0%)'   // hidden from the right
    : 'inset(100% 0% 0% 0%)'    // hidden from the bottom
  const triggers = els.map((el) => {
    gsap.set(el, { clipPath: initial, webkitClipPath: initial })
    return ScrollTrigger.create({
      trigger: el,
      start: 'top 75%',
      once: true,
      onEnter: () => {
        gsap.to(el, {
          clipPath: 'inset(0% 0% 0% 0%)',
          webkitClipPath: 'inset(0% 0% 0% 0%)',
          duration: 1.2,
          ease: 'power4.inOut',
        })
      },
    })
  })
  return () => triggers.forEach((t) => t.kill())
}

export function refreshTriggers() {
  ScrollTrigger.refresh()
}
