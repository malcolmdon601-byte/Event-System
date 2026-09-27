<template>
  <div>
    <PublicNav />
    <section class="bg-navy text-white py-16">
      <div class="max-w-7xl mx-auto px-6">
        <h1 class="text-3xl font-extrabold">Services & Packages</h1>
        <p class="text-white/60 mt-2 max-w-xl">Everything you need for a flawless event, delivered by our in-house team and trusted partners.</p>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 py-16">
      <div v-if="loading" class="text-navy/40">Loading services…</div>
      <div v-else-if="error" class="card text-danger">{{ error }}</div>
      <div v-else-if="!services.length" class="card text-center text-navy/50">No services published yet — check back soon.</div>
      <div v-else class="grid md:grid-cols-3 gap-6">
        <div v-for="svc in services" :key="svc.id" class="card flex flex-col">
          <span class="badge bg-champagne text-navy w-fit">{{ svc.category }}</span>
          <h3 class="font-bold mt-3">{{ svc.name }}</h3>
          <p class="text-sm text-navy/50 mt-1 flex-1">{{ svc.description }}</p>
          <div class="flex items-center justify-between mt-4">
            <p class="font-extrabold text-lg">UGX {{ Number(svc.price).toLocaleString() }}<span class="text-xs font-normal text-navy/40">{{ pricingLabel(svc.pricing_type) }}</span></p>
          </div>
        </div>
      </div>
      <div class="text-center mt-12">
        <router-link to="/request-quote" class="btn-gold">Request a Quote</router-link>
      </div>
    </section>
    <PublicFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import PublicNav from '@/components/PublicNav.vue'
import PublicFooter from '@/components/PublicFooter.vue'

const services = ref([])
const loading = ref(true)
const error = ref('')

function pricingLabel(type) {
  return { per_guest: ' / guest', per_hour: ' / hour', fixed: '' }[type] || ''
}

onMounted(async () => {
  try {
    const { data } = await api.get('/public/services')
    services.value = data
  } catch (e) {
    error.value = 'Unable to load services right now. Please try again shortly.'
  } finally {
    loading.value = false
  }
})
</script>
