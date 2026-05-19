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

    <section class="section">
      <div class="container-wide">
        <div class="section-label">Hosting</div>
        <h1 class="display" style="margin-bottom: 1.5rem;">
          Hosting that <span class="mark">doesn't</span> get cute.
        </h1>
        <p class="lede" style="max-width: 60ch;">
          We run managed DigitalOcean droplets for the sites we build.
          You pay what hosting costs us plus enough margin to keep the lights on.
          No upsells. No "introductory rates" that triple in year two.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
          <div
            v-for="tier in tiers"
            :key="tier.slug"
            class="tier"
            :class="{ 'tier-featured': tier.slug === 'pro' }"
          >
            <div>
              <div class="micro micro-accent" v-if="tier.slug === 'pro'">Recommended</div>
              <div class="micro" v-else>Tier · {{ tier.name }}</div>
              <h2 class="display-sm" style="margin-top: 0.75rem; color: inherit;">{{ tier.name }}</h2>
            </div>

            <div>
              <span class="tier-price">${{ tier.price_monthly }}</span>
              <span class="tier-price-unit"> / month</span>
            </div>

            <p style="font-family: var(--font-serif); font-size: 1rem; line-height: 1.55; color: inherit; opacity: 0.9;">
              {{ tier.positioning }}
            </p>

            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem;">
              <li v-for="item in tier.includes" :key="item" class="tier-li">{{ item }}</li>
            </ul>

            <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid currentColor; opacity: 0.85; font-family: var(--font-serif); font-style: italic; font-size: 14px;">
              {{ tier.best_for }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-wide">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
          <div>
            <div class="section-label">Pricing honesty</div>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;" class="prose-body">
              <li v-for="line in whats_included" :key="line" style="padding-left: 1.25rem; position: relative;">
                <span style="position: absolute; left: 0; color: var(--oxblood);">—</span>
                {{ line }}
              </li>
            </ul>
          </div>

          <div>
            <div class="section-label">What "managed" means here</div>
            <p class="prose-body">
              We provision the droplet, install nginx, install Let's Encrypt, install the
              database, tune PHP-FPM or systemd, wire the git-push deploy, set up nightly
              off-server backups, and put monitoring in place. Then we run it.
              If something breaks at 3am, we get paged, not you.
            </p>
            <p class="prose-body" style="margin-top: 1rem;">
              If you outgrow a tier, we tell you and move you up at cost.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="section-last">
      <div class="container-wide">
        <h2 class="display-md" style="margin-bottom: 1rem;">Want a domain with that?</h2>
        <p class="lede" style="max-width: 52ch; margin-bottom: 2rem;">
          We resell name.com domains at near-wholesale.
          The .com you want is probably $16 a year all in. No upsells, no surprise renewals.
        </p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <Link href="/domains" class="btn btn-primary">Search domains →</Link>
          <Link href="/contact" class="btn btn-secondary">Just talk to us</Link>
        </div>
      </div>
    </section>
  </Site>
</template>
