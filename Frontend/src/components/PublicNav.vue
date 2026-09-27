<template>
  <header class="sticky top-0 z-40 bg-navy/95 backdrop-blur text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
      <router-link to="/" class="flex items-center gap-2 font-extrabold text-lg tracking-tight">
        <span class="text-gold">Event</span>Flow
      </router-link>
      <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-white/80">
        <router-link to="/" class="hover:text-white transition" @click="mobileOpen = false">Home</router-link>
        <router-link to="/services" class="hover:text-white transition" @click="mobileOpen = false">Services</router-link>
        <router-link to="/request-quote" class="hover:text-white transition" @click="mobileOpen = false">Request a Quote</router-link>
      </nav>
      <div class="flex items-center gap-3">
        <router-link to="/login" class="btn-gold !px-3 sm:!px-4 !py-2 text-sm">Sign In</router-link>
        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-white/20 text-white md:hidden" :aria-expanded="mobileOpen" aria-controls="mobile-public-nav" aria-label="Toggle navigation" @click="mobileOpen = !mobileOpen">
          <span aria-hidden="true">{{ mobileOpen ? '✕' : '☰' }}</span>
        </button>
      </div>
    </div>
    <nav v-if="mobileOpen" id="mobile-public-nav" class="border-t border-white/10 px-4 py-3 md:hidden" aria-label="Mobile navigation">
      <div class="mx-auto flex max-w-7xl flex-col gap-1 text-sm font-medium text-white/80">
        <router-link to="/" class="rounded-lg px-3 py-3 hover:bg-white/10" @click="mobileOpen = false">Home</router-link>
        <router-link to="/services" class="rounded-lg px-3 py-3 hover:bg-white/10" @click="mobileOpen = false">Services</router-link>
        <router-link to="/request-quote" class="rounded-lg px-3 py-3 hover:bg-white/10" @click="mobileOpen = false">Request a Quote</router-link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref } from 'vue'

const mobileOpen = ref(false)
</script>
