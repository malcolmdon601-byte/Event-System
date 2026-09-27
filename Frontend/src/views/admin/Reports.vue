<template>
  <AdminShell title="Reports">
    <div v-if="loading" class="text-navy/40">Loading reports…</div>
    <div v-else class="space-y-6">
      <div class="grid sm:grid-cols-3 gap-4">
        <StatCard label="Revenue Collected" :value="'UGX ' + Number(d.stats.revenue).toLocaleString()" icon="$" accent />
        <StatCard label="Outstanding Balance" :value="'UGX ' + Number(d.stats.outstanding).toLocaleString()" icon="!" />
        <StatCard label="Confirmed Bookings" :value="d.stats.confirmed_bookings" icon="✓" />
      </div>

      <div class="card">
        <h3 class="font-bold mb-4">Monthly Revenue</h3>
        <div v-if="!d.monthly_revenue.length" class="text-sm text-navy/40">No payment history yet.</div>
        <div v-else class="space-y-2">
          <div v-for="row in d.monthly_revenue" :key="row.month" class="flex items-center gap-3">
            <span class="text-xs w-16 text-navy/50">{{ row.month }}</span>
            <div class="flex-1 h-3 bg-navy/5 rounded-full overflow-hidden"><div class="h-full bg-gold" :style="{ width: barWidth(row.total) + '%' }"></div></div>
            <span class="text-xs font-semibold w-28 text-right">UGX {{ Number(row.total).toLocaleString() }}</span>
          </div>
        </div>
      </div>

      <div class="card">
        <h3 class="font-bold mb-4">Events by Status</h3>
        <div class="grid sm:grid-cols-4 gap-3">
          <div v-for="(count, status) in d.events_by_status" :key="status" class="rounded-lg bg-navy/[0.03] p-4 text-center">
            <p class="text-2xl font-extrabold">{{ count }}</p>
            <p class="text-xs text-navy/50 capitalize mt-1">{{ status.replace('_', ' ') }}</p>
          </div>
        </div>
      </div>

      <p class="text-xs text-navy/40">Note: this report view is a simplified demo summary. A production build would add exportable PDF/Excel reports and date-range filtering.</p>
    </div>
  </AdminShell>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/api'
import AdminShell from '@/components/AdminShell.vue'
import StatCard from '@/components/StatCard.vue'

const d = ref(null)
const loading = ref(true)

const maxRevenue = computed(() => Math.max(1, ...(d.value?.monthly_revenue || []).map((r) => Number(r.total))))
function barWidth(total) { return Math.max(4, (Number(total) / maxRevenue.value) * 100) }

onMounted(async () => {
  const { data } = await api.get('/dashboard')
  d.value = data
  loading.value = false
})
</script>
