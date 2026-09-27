<template>
  <AdminShell title="Quotations">
    <div class="flex justify-between items-center mb-6 gap-3 flex-wrap">
      <select v-model="statusFilter" @change="load" class="input !w-48">
        <option value="">All statuses</option>
        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
      </select>
      <button class="btn-gold" @click="openCreate">+ New Quotation</button>
    </div>

    <div class="card !p-0 overflow-x-auto">
      <table class="w-full text-sm min-w-[700px]">
        <thead><tr class="text-left text-navy/40 text-xs uppercase border-b border-navy/5"><th class="p-4">Number</th><th class="p-4">Customer</th><th class="p-4">Total</th><th class="p-4">Valid Until</th><th class="p-4">Status</th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="p-6 text-center text-navy/40">Loading quotations…</td></tr>
          <tr v-else-if="!quotations.length"><td colspan="5" class="p-6 text-center text-navy/40">No quotations found.</td></tr>
          <tr v-for="q in quotations" :key="q.id" class="border-b border-navy/5 last:border-0">
            <td class="p-4 font-mono text-xs">{{ q.quotation_number }}</td>
            <td class="p-4">{{ q.customer?.name }}</td>
            <td class="p-4 font-semibold">UGX {{ Number(q.total).toLocaleString() }}</td>
            <td class="p-4">{{ q.valid_until ? formatDate(q.valid_until) : '—' }}</td>
            <td class="p-4"><StatusBadge :status="q.status" /></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 backdrop-blur-sm px-4">
      <div class="card max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <h3 class="font-bold text-lg mb-4">New Quotation</h3>
        <form class="space-y-4" @submit.prevent="create">
          <div>
            <label class="label">Event</label>
            <select v-model="form.event_id" class="input" required>
              <option value="">Select event</option>
              <option v-for="e in events" :key="e.id" :value="e.id">{{ e.name }} — {{ e.customer?.name }}</option>
            </select>
          </div>

          <div>
            <div class="flex justify-between items-center mb-2"><label class="label !mb-0">Line Items</label><button type="button" class="text-xs font-semibold text-gold" @click="addItem">+ Add item</button></div>
            <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-12 gap-2 mb-2">
              <input v-model="item.description" placeholder="Description" class="input col-span-6" required />
              <input v-model="item.quantity" type="number" min="1" placeholder="Qty" class="input col-span-2" required />
              <input v-model="item.unit_price" type="number" min="0" placeholder="Unit price" class="input col-span-3" required />
              <button type="button" class="col-span-1 text-danger text-lg" @click="form.items.splice(i, 1)">×</button>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div><label class="label">Discount (UGX)</label><input v-model="form.discount" type="number" min="0" class="input" /></div>
            <div><label class="label">Deposit Required (UGX)</label><input v-model="form.deposit_amount" type="number" min="0" class="input" /></div>
          </div>
          <div><label class="label">Notes</label><textarea v-model="form.notes" class="input" rows="2"></textarea></div>

          <p v-if="formError" class="text-sm text-danger">{{ formError }}</p>
          <div class="flex justify-end gap-3"><button type="button" class="btn-outline" @click="showForm = false">Cancel</button><button class="btn-gold" :disabled="saving">{{ saving ? 'Creating…' : 'Create Quotation' }}</button></div>
        </form>
      </div>
    </div>

    <Toast :message="toast.state.message" :type="toast.state.type" />
  </AdminShell>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import AdminShell from '@/components/AdminShell.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const quotations = ref([])
const events = ref([])
const loading = ref(true)
const statusFilter = ref('')
const showForm = ref(false)
const saving = ref(false)
const formError = ref('')
const statuses = ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired']

const form = ref({ event_id: '', discount: 0, deposit_amount: 0, notes: '', items: [{ description: '', quantity: 1, unit_price: '' }] })

function formatDate(d) { return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }
function addItem() { form.value.items.push({ description: '', quantity: 1, unit_price: '' }) }

async function load() {
  loading.value = true
  const params = {}
  if (statusFilter.value) params.status = statusFilter.value
  const { data } = await api.get('/quotations', { params })
  quotations.value = data.data
  loading.value = false
}

async function openCreate() {
  formError.value = ''
  if (!events.value.length) events.value = (await api.get('/events', { params: { status: '' } })).data.data
  showForm.value = true
}

async function create() {
  saving.value = true
  formError.value = ''
  try {
    await api.post('/quotations', form.value)
    showForm.value = false
    form.value = { event_id: '', discount: 0, deposit_amount: 0, notes: '', items: [{ description: '', quantity: 1, unit_price: '' }] }
    toast.show('Quotation created and sent.')
    await load()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Please check the highlighted fields.'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>
