<template>
  <AdminShell title="Dashboard">
    <div v-if="loading" class="text-navy/40">Loading dashboard…</div>
    <div v-else-if="error" class="card text-danger">{{ error }}</div>
    <div v-else>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard label="Total Events" :value="d.stats.total_events" icon="▤" accent />
        <StatCard label="Upcoming Events" :value="d.stats.upcoming_events" icon="◆" />
        <StatCard label="Pending Enquiries" :value="d.stats.pending_enquiries" icon="?" />
        <StatCard label="Confirmed Bookings" :value="d.stats.confirmed_bookings" icon="✓" />
        <StatCard label="Revenue Collected" :value="'UGX ' + Number(d.stats.revenue).toLocaleString()" icon="$" accent />
        <StatCard label="Outstanding Balance" :value="'UGX ' + Number(d.stats.outstanding).toLocaleString()" icon="!" />
      </div>

      <div class="grid lg:grid-cols-3 gap-6 mt-8">
        <div class="lg:col-span-2 card">
          <h2 class="font-bold mb-4">Upcoming Events</h2>
          <div v-if="!d.upcoming_events.length" class="text-navy/40 text-sm">No upcoming events scheduled.</div>
          <table v-else class="w-full text-sm">
            <thead><tr class="text-left text-navy/40 text-xs uppercase"><th class="pb-2">Event</th><th class="pb-2">Date</th><th class="pb-2">Status</th></tr></thead>
            <tbody>
              <tr v-for="ev in d.upcoming_events" :key="ev.id" class="border-t border-navy/5">
                <td class="py-2.5"><router-link :to="`/admin/events/${ev.id}`" class="font-medium hover:text-gold">{{ ev.name }}</router-link><br /><span class="text-xs text-navy/40">{{ ev.customer?.name }}</span></td>
                <td class="py-2.5">{{ formatDate(ev.event_date) }}</td>
                <td class="py-2.5"><StatusBadge :status="ev.status" /></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card">
          <h2 class="font-bold mb-4">Events by Status</h2>
          <div class="space-y-2">
            <div v-for="(count, status) in d.events_by_status" :key="status" class="flex items-center justify-between text-sm">
              <StatusBadge :status="status" />
              <span class="font-semibold">{{ count }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card mt-6">
        <h2 class="font-bold mb-4">Recent Payments</h2>
        <div v-if="!d.recent_payments.length" class="text-navy/40 text-sm">No payments recorded yet.</div>
        <table v-else class="w-full text-sm">
          <thead><tr class="text-left text-navy/40 text-xs uppercase"><th class="pb-2">Reference</th><th class="pb-2">Customer</th><th class="pb-2">Method</th><th class="pb-2 text-right">Amount</th></tr></thead>
          <tbody>
            <tr v-for="p in d.recent_payments" :key="p.id" class="border-t border-navy/5">
              <td class="py-2.5 font-mono text-xs">{{ p.payment_reference }}</td>
              <td class="py-2.5">{{ p.customer?.name }}</td>
              <td class="py-2.5 capitalize">{{ p.method.replace('_', ' ') }}</td>
              <td class="py-2.5 text-right font-semibold">UGX {{ Number(p.amount).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminShell>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import AdminShell from '@/components/AdminShell.vue'
import StatCard from '@/components/StatCard.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const d = ref(null)
const loading = ref(true)
const error = ref('')

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await api.get('/dashboard')
    d.value = data
  } catch (e) {
    error.value = 'Unable to load dashboard statistics.'
  } finally {
    loading.value = false
  }
})
</script>
