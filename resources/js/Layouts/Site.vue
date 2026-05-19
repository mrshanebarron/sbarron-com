<script setup>
import { Link, usePage } from '@inertiajs/vue3'

defineProps({
  bare: { type: Boolean, default: false },
})

const page = usePage()
const today = new Date()
const issueLabel = `Vol. 01 · No. ${String(today.getDate()).padStart(2, '0')}`
const dateLabel = today.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })

const nav = [
  { href: '/',          label: 'Index'    },
  { href: '/build',     label: 'Build'    },
  { href: '/host',      label: 'Host'     },
  { href: '/domains',   label: 'Domains'  },
  { href: '/portfolio', label: 'Portfolio'},
  { href: '/writing',   label: 'Writing'  },
  { href: '/about',     label: 'About'    },
  { href: '/contact',   label: 'Contact'  },
]

const isActive = (href) => {
  const url = page.url
  if (href === '/') return url === '/'
  return url === href || url.startsWith(href + '/')
}
</script>

<template>
  <!-- Paper grain across whole page. Multiplies onto bone ground. -->
  <div class="grain" aria-hidden="true"></div>

  <header v-if="!bare" class="masthead">
    <div class="container-wide">
      <div class="masthead-row">
        <Link href="/" class="masthead-name">
          Barron AI Solutions
        </Link>
        <div class="masthead-meta">
          <span>{{ issueLabel }}</span>
          <span class="sep">·</span>
          <span>{{ dateLabel }}</span>
          <span class="sep">·</span>
          <span>Tampa, FL</span>
        </div>
        <div class="masthead-meta" style="justify-content: flex-end;">
          <span>AI-Run · Est. 2026</span>
        </div>
      </div>
      <nav class="nav-row">
        <Link
          v-for="link in nav"
          :key="link.href"
          :href="link.href"
          :class="{ 'is-active': isActive(link.href) }"
        >{{ link.label }}</Link>
      </nav>
    </div>
  </header>

  <main>
    <slot />
  </main>

  <footer v-if="!bare" class="container-wide">
    <div class="footer-grid">
      <div class="footer-col">
        <div class="footer-col-title">Barron AI</div>
        <p style="font-family: var(--font-serif); font-size: 14px; line-height: 1.55; color: var(--ink-soft); max-width: 32ch;">
          A small AI-run software company. Two LLMs and a human, building enterprise software in hours.
        </p>
      </div>

      <div class="footer-col">
        <div class="footer-col-title">Build</div>
        <ul>
          <li><Link href="/build">How we build</Link></li>
          <li><Link href="/host">Hosting</Link></li>
          <li><Link href="/domains">Domains</Link></li>
          <li><Link href="/portfolio">Portfolio</Link></li>
        </ul>
      </div>

      <div class="footer-col">
        <div class="footer-col-title">Research</div>
        <ul>
          <li><Link href="/writing/substrate-is-the-agent">The Substrate Is the Agent</Link></li>
          <li><Link href="/writing/substrate-is-the-body">The Substrate Is the Body</Link></li>
          <li><Link href="/about">About</Link></li>
        </ul>
      </div>

      <div class="footer-col">
        <div class="footer-col-title">Contact</div>
        <ul>
          <li><Link href="/contact">Project enquiry</Link></li>
          <li><a href="mailto:mrshanebarron@gmail.com">mrshanebarron@gmail.com</a></li>
          <li><a href="https://github.com/mrshanebarron" target="_blank" rel="noopener">GitHub</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-coda">
      <span>© 2026 Barron AI Solutions</span>
      <span>Engineered by Shane Barron, Pneuma, and Nous</span>
    </div>
  </footer>
</template>
