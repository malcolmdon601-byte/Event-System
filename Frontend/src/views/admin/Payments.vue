<template>
  <AdminShell title="Payments">
    <div class="flex justify-between items-center mb-6">
      <p class="text-sm text-navy/50">All payments are <span class="font-semibold">simulated for this demo</span> — no real transactions are processed.</p>
      <button class="btn-gold" @click="openCreate">+ Record Payment</button>
    </div>

    <div class="card !p-0 overflow-x-auto">
      <table class="w-full text-sm min-w-[700px]">
        <thead><tr class="text-left text-navy/40 text-xs uppercase border-b border-navy/5"><th class="p-4">Reference</th><th class="p-4">Customer</th><th class="p-4">Event</th><th class="p-4">Method</th><th class="p-4 text-right">Amount</th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="p-6 text-center text-navy/40">Loading payments…</td></tr>
          <tr v-else-if="!payments.length"><td colspan="5" class="p-6 text-center text-navy/40">No payments recorded.</td></tr>
          <tr v-for="p in payments" :key="p.id" class="border-b border-navy/5 last:border-0">
            <td class="p-4 font-mono text-xs">{{ p.payment_reference }}</td>
            <td class="p-4">{{ p.customer?.name }}</td>
            <td class="p-4">{{ p.event?.name }}</td>
            <td class="p-4 capitalize">{{ p.method.replace('_', ' ') }}</td>
            <td class="p-4 text-right font-semibold">UGX {{ Number(p.amount).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 backdrop-blur-sm px-4">
      <div class="card max-w-md w-full">
        <h3 class="font-bold mb-1">Record Payment</h3>
        <p class="text-xs text-navy/40 mb-4">Demo/simulated transaction — no real payment gateway is contacted.</p>
        <form class="space-y-4" @submit.prevent="create">
          <div>
            <label class="label">Invoice</label>
            <select v-model="form.invoice_id" class="input" required @change="onInvoiceChange">
              <option value="">Select invoice</option>
              <option v-for="i in invoices" :key="i.id" :value="i.id">{{ i.invoice_number }} — Balance UGX {{ Number(i.balance).toLocaleString() }}</option>
            </select>
          </div>
          <div><label class="label">Amount (UGX)</label><input v-model="form.amount" type="number" min="1" class="input" required /></div>
          <div>
            <label class="label">Method</label>
            <select v-model="form.method" class="input">
              <option value="mobile_money">Mobile Money</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="card">Card</option>
              <option value="cash">Cash</option>
            </select>
          </div>
          <p v-if="formError" class="text-sm text-danger">{{ formError }}</p>
          <div class="flex justify-end gap-3"><button type="button" class="btn-outline" @click="showForm = false">Cancel</button><button class="btn-gold" :disabled="saving">{{ saving ? 'Recording…' : 'Record Payment' }}</button></div>
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
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const payments = ref([])
const invoices = ref([])
const loading = ref(true)
const showForm = ref(false)
const saving = ref(false)
const formError = ref('')

const form = ref({ invoice_id: '', event_id: '', customer_id: '', amount: '', method: 'mobile_money' })

async function load() {
  loading.value = true
  const { data } = await api.get('/payments')
  payments.value = data.data
  loading.value = false
}

async function openCreate() {
  formError.value = ''
  if (!invoices.value.length) {
    const events = (await api.get('/events')).data.data
    const allInvoices = []
    for (const ev of events) {
      const full = (await api.get(`/events/${ev.id}`)).data
      full.invoices.forEach((inv) => { if (inv.balance > 0) allInvoices.push({ ...inv, event_id: ev.id, customer_id: ev.customer_id }) })
    }
    invoices.value = allInvoices
  }
  showForm.value = true
}

function onInvoiceChange() {
  const inv = invoices.value.find((i) => i.id === form.value.invoice_id)
  if (inv) { form.value.event_id = inv.event_id; form.value.customer_id = inv.customer_id }
}

async function create() {
  saving.value = true
  formError.value = ''
  try {
    await api.post('/payments', form.value)
    showForm.value = false
    form.value = { invoice_id: '', event_id: '', customer_id: '', amount: '', method: 'mobile_money' }
    toast.show('Payment recorded (simulated).')
    await load()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Please check the highlighted fields.'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>
