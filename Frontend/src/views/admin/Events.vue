<template>
  <AdminShell title="Events">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <div class="flex gap-2 flex-wrap">
        <input v-model="search" @keyup.enter="load" placeholder="Search events…" class="input !w-56" />
        <select v-model="statusFilter" @change="load" class="input !w-44">
          <option value="">All statuses</option>
          <option v-for="s in statuses" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
        </select>
      </div>
      <button class="btn-gold" @click="openCreate">+ New Event</button>
    </div>

    <div class="card !p-0 overflow-x-auto">
      <table class="w-full text-sm min-w-[700px]">
        <thead><tr class="text-left text-navy/40 text-xs uppercase border-b border-navy/5"><th class="p-4">Event</th><th class="p-4">Customer</th><th class="p-4">Date</th><th class="p-4">Guests</th><th class="p-4">Status</th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="p-6 text-center text-navy/40">Loading events…</td></tr>
          <tr v-else-if="!events.length"><td colspan="5" class="p-6 text-center text-navy/40">No events found.</td></tr>
          <tr v-for="ev in events" :key="ev.id" class="border-b border-navy/5 last:border-0 hover:bg-navy/[0.02] cursor-pointer" @click="$router.push(`/admin/events/${ev.id}`)">
            <td class="p-4 font-medium">{{ ev.name }}</td>
            <td class="p-4">{{ ev.customer?.name }}</td>
            <td class="p-4">{{ formatDate(ev.event_date) }}</td>
            <td class="p-4">{{ ev.guest_count ?? '—' }}</td>
            <td class="p-4"><StatusBadge :status="ev.status" /></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create modal -->
    <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 backdrop-blur-sm px-4">
      <div class="card max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <h3 class="font-bold text-lg mb-4">New Event</h3>
        <form class="space-y-4" @submit.prevent="create">
          <div>
            <label class="label">Customer</label>
            <select v-model="form.customer_id" class="input" required>
              <option value="">Select customer</option>
              <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Event Name</label>
            <input v-model="form.name" class="input" required />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="label">Date</label><input v-model="form.event_date" type="date" class="input" required /></div>
            <div><label class="label">Guests</label><input v-model="form.guest_count" type="number" min="0" class="input" /></div>
          </div>
          <div><label class="label">Venue</label><input v-model="form.venue" class="input" /></div>
          <p v-if="formError" class="text-sm text-danger">{{ formError }}</p>
          <div class="flex justify-end gap-3">
            <button type="button" class="btn-outline" @click="showCreate = false">Cancel</button>
            <button class="btn-gold" :disabled="saving">{{ saving ? 'Creating…' : 'Create Event' }}</button>
          </div>
        </form>
      </div>
    </div>
  </AdminShell>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import AdminShell from '@/components/AdminShell.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const events = ref([])
const customers = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const showCreate = ref(false)
const saving = ref(false)
const formError = ref('')

const statuses = ['enquiry', 'quotation', 'deposit', 'confirmed', 'planning', 'ready', 'completed', 'cancelled']
const form = ref({ customer_id: '', name: '', event_date: '', guest_count: '', venue: '' })

function formatDate(d) { return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }

async function load() {
  loading.value = true
  const params = {}
  if (search.value) params.search = search.value
  if (statusFilter.value) params.status = statusFilter.value
  const { data } = await api.get('/events', { params })
  events.value = data.data
  loading.value = false
}

async function openCreate() {
  formError.value = ''
  if (!customers.value.length) {
    const { data } = await api.get('/customers', { params: { search: '' } })
    customers.value = data.data
  }
  showCreate.value = true
}

async function create() {
  saving.value = true
  formError.value = ''
  try {
    await api.post('/events', form.value)
    showCreate.value = false
    form.value = { customer_id: '', name: '', event_date: '', guest_count: '', venue: '' }
    await load()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Unable to create event. Please check the highlighted fields.'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>
