<template>
  <AdminShell title="Customers">
    <div class="flex justify-between items-center mb-6 gap-3 flex-wrap">
      <input v-model="search" @keyup.enter="load" placeholder="Search customers…" class="input !w-64" />
      <button class="btn-gold" @click="openCreate">+ New Customer</button>
    </div>

    <div class="card !p-0 overflow-x-auto">
      <table class="w-full text-sm min-w-[600px]">
        <thead><tr class="text-left text-navy/40 text-xs uppercase border-b border-navy/5"><th class="p-4">Name</th><th class="p-4">Email</th><th class="p-4">Phone</th><th class="p-4">Events</th><th class="p-4"></th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="p-6 text-center text-navy/40">Loading customers…</td></tr>
          <tr v-else-if="!customers.length"><td colspan="5" class="p-6 text-center text-navy/40">No customers found.</td></tr>
          <tr v-for="c in customers" :key="c.id" class="border-b border-navy/5 last:border-0">
            <td class="p-4 font-medium">{{ c.name }}</td>
            <td class="p-4 text-navy/60">{{ c.email || '—' }}</td>
            <td class="p-4 text-navy/60">{{ c.phone || '—' }}</td>
            <td class="p-4">{{ c.events_count }}</td>
            <td class="p-4 text-right">
              <button class="btn-outline !py-1 !px-3 text-xs" @click="edit(c)">Edit</button>
              <button class="btn-outline !py-1 !px-3 text-xs !text-danger !border-danger/30 ml-2" @click="confirmDelete(c)">Archive</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 backdrop-blur-sm px-4">
      <div class="card max-w-md w-full">
        <h3 class="font-bold mb-4">{{ form.id ? 'Edit Customer' : 'New Customer' }}</h3>
        <form class="space-y-4" @submit.prevent="save">
          <div><label class="label">Name</label><input v-model="form.name" class="input" required /></div>
          <div><label class="label">Email</label><input v-model="form.email" type="email" class="input" /></div>
          <div><label class="label">Phone</label><input v-model="form.phone" class="input" /></div>
          <div><label class="label">Address</label><textarea v-model="form.address" class="input" rows="2"></textarea></div>
          <p v-if="formError" class="text-sm text-danger">{{ formError }}</p>
          <div class="flex justify-end gap-3"><button type="button" class="btn-outline" @click="showForm = false">Cancel</button><button class="btn-gold" :disabled="saving">{{ saving ? 'Saving…' : 'Save' }}</button></div>
        </form>
      </div>
    </div>

    <ConfirmDialog :open="!!toDelete" title="Archive customer?" message="This can be restored later but will hide the customer from active lists." confirm-label="Archive" @cancel="toDelete = null" @confirm="doDelete" />
    <Toast :message="toast.state.message" :type="toast.state.type" />
  </AdminShell>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import AdminShell from '@/components/AdminShell.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const customers = ref([])
const loading = ref(true)
const search = ref('')
const showForm = ref(false)
const saving = ref(false)
const formError = ref('')
const toDelete = ref(null)

const form = ref({ id: null, name: '', email: '', phone: '', address: '' })

async function load() {
  loading.value = true
  const { data } = await api.get('/customers', { params: { search: search.value } })
  customers.value = data.data
  loading.value = false
}

function openCreate() {
  form.value = { id: null, name: '', email: '', phone: '', address: '' }
  formError.value = ''
  showForm.value = true
}

function edit(c) {
  form.value = { id: c.id, name: c.name, email: c.email, phone: c.phone, address: c.address }
  formError.value = ''
  showForm.value = true
}

async function save() {
  saving.value = true
  formError.value = ''
  try {
    if (form.value.id) await api.put(`/customers/${form.value.id}`, form.value)
    else await api.post('/customers', form.value)
    showForm.value = false
    toast.show('Customer saved.')
    await load()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Please check the highlighted fields.'
  } finally {
    saving.value = false
  }
}

function confirmDelete(c) { toDelete.value = c }
async function doDelete() {
  await api.delete(`/customers/${toDelete.value.id}`)
  toDelete.value = null
  toast.show('Customer archived.')
  await load()
}

onMounted(load)
</script>
