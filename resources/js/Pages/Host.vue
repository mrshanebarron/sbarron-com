<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Site from '@/Layouts/Site.vue'

defineProps({
  tiers: { type: Array, default: () => [] },
  whats_included: { type: Array, default: () => [] },
})
</script>

<template>
  <Site>
    <Head title="Hosting — Barron AI Solutions" />

    <article class="relative max-w-[1100px] mx-auto px-[clamp(1.5rem,4vw,4rem)] pt-12 pb-32">
      <div class="hud-rail">
        <span class="dot"></span>
        <span>HOST / SECT.09 INFRASTRUCTURE</span>
        <span class="sep">::</span>
        <span class="mut">Managed DigitalOcean — pass-through pricing</span>
      </div>

      <header class="mt-2 mb-12 max-w-[60ch]">
        <h1 class="display-md" style="font-size: clamp(2.5rem, 5.5vw, 5.5rem); color: var(--fg-stage-1);">
          Hosting that <span class="stroke" style="color: var(--color-amber);">doesn't</span> get cute.
        </h1>
        <p class="mt-6 font-serif text-lg leading-relaxed" style="color: rgba(243, 234, 217, 0.75);">
          We run managed DigitalOcean droplets for the sites we build. You pay what hosting costs us
          plus enough margin to keep the lights on. No upsells. No "introductory rates."
        </p>
      </header>

      <!-- Tier cards -->
      <div class="grid gap-6 md:grid-cols-2 mt-8">
        <div
          v-for="tier in tiers"
          :key="tier.slug"
          class="relative p-8 border border-[color:var(--color-rule)]"
          :class="{ 'border-amber': tier.slug === 'pro' }"
          :style="tier.slug === 'pro'
            ? 'background: rgba(255, 154, 60, 0.04); border-color: var(--color-amber);'
            : 'background: rgba(20, 24, 31, 0.45);'"
        >
          <div class="absolute top-4 right-4 font-mono text-[9px] uppercase tracking-[0.22em]"
               v-if="tier.slug === 'pro'"
               style="color: var(--color-amber);">
            Recommended
          </div>

          <div class="font-mono text-[10px] uppercase tracking-[0.24em]" style="color: var(--color-amber);">
            {{ tier.name }}
          </div>

          <div class="mt-3 flex items-baseline gap-2">
            <span class="font-serif font-semibold leading-none" style="font-size: 4rem; color: var(--fg-stage-1);">
              ${{ tier.price_monthly }}
            </span>
            <span class="font-mono text-sm" style="color: rgba(243,234,217,0.55);">/ month</span>
          </div>

          <p class="mt-3 font-serif text-base" style="color: rgba(243, 234, 217, 0.78);">
            {{ tier.positioning }}
          </p>

          <ul class="mt-6 space-y-2.5 font-mono text-[13px]" style="color: rgba(243,234,217,0.82);">
            <li v-for="item in tier.includes" :key="item" class="flex gap-3">
              <span class="mt-1.5 inline-block w-1 h-1 rounded-full flex-none" style="background: var(--color-amber);"></span>
              <span>{{ item }}</span>
            </li>
          </ul>

          <div class="mt-8 pt-6 border-t border-[color:var(--color-rule)] font-serif text-sm italic"
               style="color: rgba(243,234,217,0.7);">
            {{ tier.best_for }}
          </div>
        </div>
      </div>

      <!-- What's included / honesty -->
      <section class="mt-16 grid md:grid-cols-2 gap-10">
        <div>
          <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-4" style="color: var(--color-cyan);">
            Pricing honesty
          </div>
          <ul class="space-y-4 font-serif text-base leading-relaxed" style="color: rgba(243, 234, 217, 0.78);">
            <li v-for="line in whats_included" :key="line">— {{ line }}</li>
          </ul>
        </div>
        <div>
          <div class="font-mono text-[10px] uppercase tracking-[0.24em] mb-4" style="color: var(--color-cyan);">
            What "managed" means here
          </div>
          <p class="font-serif text-base leading-relaxed" style="color: rgba(243, 234, 217, 0.78);">
            We provision the droplet, install nginx, install Let's Encrypt, install the database,
            tune PHP-FPM or systemd, wire the git-push deploy, set up nightly off-server backups,
            and put the monitoring in place. Then we run it. If something breaks at 3am, we get
            paged — not you.
          </p>
          <p class="mt-4 font-serif text-base leading-relaxed" style="color: rgba(243, 234, 217, 0.78);">
            If you outgrow a tier, we tell you and move you up at cost. We do not run silent
            upsell timers on your bill.
          </p>
        </div>
      </section>

      <section class="mt-16 pt-12 border-t border-[color:var(--color-rule)]">
        <h3 class="font-serif text-2xl sm:text-3xl font-semibold" style="color: var(--fg-stage-1);">
          Want a domain with that?
        </h3>
        <p class="mt-3 font-serif text-base max-w-[55ch]" style="color: rgba(243,234,217,0.75);">
          We resell name.com domains at $3 over wholesale. The .com you want is probably $16/year all in.
        </p>
        <div class="mt-6 flex flex-wrap gap-4">
          <Link href="/domains" class="inline-flex items-center gap-3 px-6 py-3 font-mono text-[11px] uppercase tracking-[0.24em]"
                style="background: var(--color-amber); color: #14181f;">
            Search domains →
          </Link>
          <Link href="/contact" class="inline-flex items-center gap-3 px-6 py-3 font-mono text-[11px] uppercase tracking-[0.24em] border border-[color:var(--color-amber)]"
                style="color: var(--color-amber);">
            Just talk to us
          </Link>
        </div>
      </section>
    </article>
  </Site>
</template>
