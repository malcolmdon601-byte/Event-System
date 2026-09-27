<template>
  <div class="min-h-screen bg-bg">
    <header class="bg-navy text-white h-16 flex items-center px-6 justify-between">
      <span class="font-extrabold"><span class="text-gold">Event</span>Flow <span class="text-white/40 font-normal text-sm">Customer Portal</span></span>
      <button class="btn-outline !border-white/20 !text-white hover:!bg-white/10 !py-1.5" @click="logout">Sign Out</button>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8" v-if="!loading && data">
      <h1 class="text-2xl font-extrabold">Welcome back, {{ data.customer.name.split(' ')[0] }}</h1>
      <p class="text-navy/50 text-sm mt-1">Here's what's happening with your events.</p>

      <div class="grid md:grid-cols-3 gap-4 mt-6">
        <StatCard label="Next Event" :value="data.next_event ? formatDate(data.next_event.event_date) : '—'" :hint="data.next_event?.name" icon="◆" accent />
        <StatCard label="Outstanding Balance" :value="'UGX ' + Number(data.outstanding_balance).toLocaleString()" icon="$" />
        <StatCard label="Total Events" :value="data.events.length" icon="▤" />
      </div>

      <section class="mt-10">
        <h2 class="font-bold text-lg mb-4">Your Events</h2>
        <div v-if="!data.events.length" class="card text-center text-navy/50">No events yet. <router-link to="/" class="text-gold font-semibold">Request a quote →</router-link></div>
        <div v-else class="space-y-4">
          <div v-for="ev in data.events" :key="ev.id" class="card">
            <div class="flex items-center justify-between flex-wrap gap-2">
              <div>
                <h3 class="font-bold">{{ ev.name }}</h3>
                <p class="text-sm text-navy/50">{{ formatDate(ev.event_date) }} · {{ ev.venue || 'Venue TBC' }}</p>
              </div>
              <StatusBadge :status="ev.status" />
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
              <span v-for="(step, i) in timeline" :key="step" class="badge"
                :class="timelineIndex(ev.status) >= i ? 'bg-gold/20 text-navy' : 'bg-navy/5 text-navy/30'">
                {{ step }}
              </span>
            </div>
          </div>
        </div>
      </section>

      <section class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
          <h2 class="font-bold text-lg mb-4">Quotations</h2>
          <div v-if="!data.quotations.length" class="card text-center text-navy/50 text-sm">No quotations yet.</div>
          <div v-for="q in data.quotations" :key="q.id" class="card mb-3">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-semibold text-sm">{{ q.quotation_number }}</p>
                <p class="text-xs text-navy/40">Valid until {{ formatDate(q.valid_until) }}</p>
              </div>
              <StatusBadge :status="q.status" />
            </div>
            <p class="font-extrabold mt-2">UGX {{ Number(q.total).toLocaleString() }}</p>
            <div v-if="q.status === 'sent' || q.status === 'viewed'" class="flex gap-2 mt-3">
              <button class="btn-gold !py-1.5 !px-3 text-xs" :disabled="acting === q.id" @click="accept(q)">Accept Quotation</button>
              <button class="btn-outline !py-1.5 !px-3 text-xs" :disabled="acting === q.id" @click="reject(q)">Decline</button>
            </div>
          </div>
        </div>

        <div>
          <h2 class="font-bold text-lg mb-4">Payment History</h2>
          <div v-if="!data.payments.length" class="card text-center text-navy/50 text-sm">No payments recorded yet.</div>
          <table v-else class="w-full text-sm card !p-0 overflow-hidden">
            <tbody>
              <tr v-for="p in data.payments" :key="p.id" class="border-b border-navy/5 last:border-0">
                <td class="p-3">{{ formatDate(p.paid_at) }}</td>
                <td class="p-3 capitalize">{{ p.method.replace('_', ' ') }}</td>
                <td class="p-3 text-right font-semibold">UGX {{ Number(p.amount).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <div v-else-if="loading" class="max-w-6xl mx-auto px-6 py-16 text-navy/40">Loading your dashboard…</div>
    <div v-else class="max-w-6xl mx-auto px-6 py-16 card text-center text-danger">{{ error }}</div>

    <Toast :message="toast.state.message" :type="toast.state.type" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import StatCard from '@/components/StatCard.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'

const auth = useAuthStore()
const router = useRouter()
const toast = useToast()

const data = ref(null)
const loading = ref(true)
const error = ref('')
const acting = ref(null)

const timeline = ['Enquiry', 'Quotation', 'Deposit', 'Confirmed', 'Planning', 'Ready', 'Completed']
const statusOrder = ['enquiry', 'quotation', 'deposit', 'confirmed', 'planning', 'ready', 'completed']
function timelineIndex(status) { return statusOrder.indexOf(status) }

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function load() {
  loading.value = true
  try {
    const { data: res } = await api.get('/portal/dashboard')
    data.value = res
  } catch (e) {
    error.value = e.response?.data?.message || 'Unable to load your dashboard.'
  } finally {
    loading.value = false
  }
}

async function accept(q) {
  acting.value = q.id
  try {
    await api.post(`/quotations/${q.id}/accept`)
    toast.show('Quotation accepted! A deposit invoice has been created.')
    await load()
  } catch (e) {
    toast.show(e.response?.data?.message || 'Unable to accept quotation.', 'error')
  } finally {
    acting.value = null
  }
}

async function reject(q) {
  acting.value = q.id
  try {
    await api.post(`/quotations/${q.id}/reject`)
    toast.show('Quotation declined.')
    await load()
  } finally {
    acting.value = null
  }
}

async function logout() {
  await auth.logout()
  router.push('/login')
}

onMounted(load)
</script>
