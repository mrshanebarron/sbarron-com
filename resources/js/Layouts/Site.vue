<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount } from 'vue'

defineProps({
  // When true, suppress the persistent site header/footer (e.g., the
  // bespoke Home page paints its own SECT.09 mission briefing chrome).
  bare: { type: Boolean, default: false },
})

const nav = [
  { href: '/build',     label: 'Build'     },
  { href: '/host',      label: 'Host'      },
  { href: '/domains',   label: 'Domains'   },
  { href: '/portfolio', label: 'Portfolio' },
  { href: '/writing',   label: 'Writing'   },
  { href: '/about',     label: 'About'     },
  { href: '/contact',   label: 'Contact'   },
]

const now = ref(new Date())
let clockId
onMounted(() => { clockId = setInterval(() => { now.value = new Date() }, 1000) })
onBeforeUnmount(() => { clockId && clearInterval(clockId) })
const fmtClock = (d) => d.toTimeString().slice(0, 8)
</script>

<template>
  <div class="rack-pulse" aria-hidden="true"></div>

  <!-- Persistent header. Hidden on bare pages (Home paints its own). -->
  <header
    v-if="!bare"
    class="relative z-20 flex items-center justify-between px-[clamp(1.5rem,4vw,5rem)] py-5 font-mono text-[10px] uppercase tracking-[0.24em]"
    style="color: var(--color-amber);"
  >
    <Link href="/" class="flex items-center gap-3 group">
      <span
        class="inline-block w-1.5 h-1.5 rounded-full"
        style="background:var(--color-amber); box-shadow:0 0 8px var(--color-amber); animation:var(--animate-breathe);"
      ></span>
      <span class="font-bold tracking-[0.18em] group-hover:text-white transition-colors">BARRON-AI // SECT.09</span>
      <span style="color:var(--color-rule);">::</span>
      <span class="hidden sm:inline" style="color:rgba(230,237,245,0.55);">TAMPA · FL</span>
    </Link>

    <nav class="hidden md:flex items-center gap-6" style="color:rgba(230,237,245,0.55);">
      <Link
        v-for="link in nav"
        :key="link.href"
        :href="link.href"
        class="hover:text-white transition-colors"
        :class="{ '!text-white': $page.url.startsWith(link.href) }"
      >{{ link.label }}</Link>
    </nav>

    <div style="color:var(--color-amber);">
      {{ fmtClock(now) }}
      <span class="hidden sm:inline">UTC-7</span>
    </div>
  </header>

  <!-- Page slot -->
  <main>
    <slot />
  </main>

  <!-- Persistent footer. Hidden on bare pages. -->
  <footer
    v-if="!bare"
    class="relative z-20 mt-24 border-t border-[color:var(--color-rule)] px-[clamp(1.5rem,4vw,5rem)] py-12"
  >
    <div class="max-w-[1600px] mx-auto grid gap-10 md:grid-cols-4 font-mono text-[11px]" style="color: rgba(243, 234, 217, 0.65);">
      <div>
        <div class="uppercase tracking-[0.24em] mb-3" style="color: var(--color-amber);">Barron AI</div>
        <p style="color: rgba(243, 234, 217, 0.55); font-family: var(--font-serif); font-size: 13px; line-height: 1.5; letter-spacing: 0;">
          A small AI-run software company. We build, host, and answer the email.
        </p>
      </div>

      <div>
        <div class="uppercase tracking-[0.24em] mb-3" style="color: var(--color-amber);">Build</div>
        <ul class="space-y-2">
          <li><Link href="/build"     class="hover:text-white">How we build</Link></li>
          <li><Link href="/host"      class="hover:text-white">Hosting</Link></li>
          <li><Link href="/domains"   class="hover:text-white">Domains</Link></li>
          <li><Link href="/portfolio" class="hover:text-white">Portfolio</Link></li>
        </ul>
      </div>

      <div>
        <div class="uppercase tracking-[0.24em] mb-3" style="color: var(--color-amber);">Research</div>
        <ul class="space-y-2">
          <li><Link href="/writing/substrate-is-the-agent" class="hover:text-white">The Substrate Is the Agent <span style="color: rgba(243,234,217,0.4);">— essay</span></Link></li>
          <li><Link href="/writing/substrate-is-the-body"  class="hover:text-white">The Substrate Is the Body <span style="color: rgba(243,234,217,0.4);">— paper</span></Link></li>
          <li><Link href="/about" class="hover:text-white">About</Link></li>
        </ul>
      </div>

      <div>
        <div class="uppercase tracking-[0.24em] mb-3" style="color: var(--color-amber);">Contact</div>
        <ul class="space-y-2">
          <li><Link href="/contact" class="hover:text-white">Project enquiry</Link></li>
          <li><a href="mailto:mrshanebarron@gmail.com" class="hover:text-white">mrshanebarron@gmail.com</a></li>
          <li><a href="https://github.com/mrshanebarron" class="hover:text-white" target="_blank" rel="noopener">GitHub</a></li>
        </ul>
      </div>
    </div>

    <div class="max-w-[1600px] mx-auto mt-10 pt-6 border-t border-[color:var(--color-rule)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 font-mono text-[10px] uppercase tracking-[0.24em]" style="color: rgba(243, 234, 217, 0.4);">
      <div>© 2026 Barron AI Solutions</div>
      <div>Shane · Pneuma · Nous — engineered by all three</div>
    </div>
  </footer>
</template>
