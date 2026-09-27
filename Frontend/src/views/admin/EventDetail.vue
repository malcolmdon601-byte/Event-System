<template>
  <AdminShell :title="event?.name || 'Event'">
    <div v-if="loading" class="text-navy/40">Loading event…</div>
    <div v-else-if="!event" class="card text-danger">Event not found.</div>
    <div v-else>
      <div class="card flex flex-wrap items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-3">
            <h2 class="text-xl font-bold">{{ event.name }}</h2>
            <StatusBadge :status="event.status" />
          </div>
          <p class="text-sm text-navy/50 mt-1">{{ event.customer?.name }} · {{ formatDate(event.event_date) }} · {{ event.venue || 'Venue TBC' }}</p>
        </div>
        <select v-model="event.status" @change="updateStatus" class="input !w-48">
          <option v-for="s in statuses" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
        </select>
      </div>

      <div class="flex gap-2 flex-wrap mt-6 border-b border-navy/10">
        <button v-for="t in tabs" :key="t" class="px-4 py-2 text-sm font-semibold border-b-2 -mb-px transition"
          :class="tab === t ? 'border-gold text-navy' : 'border-transparent text-navy/40 hover:text-navy/70'" @click="tab = t">
          {{ t }}
        </button>
      </div>

      <!-- Overview -->
      <div v-if="tab === 'Overview'" class="grid md:grid-cols-2 gap-6 mt-6">
        <div class="card">
          <h3 class="font-bold mb-3">Details</h3>
          <dl class="text-sm space-y-2">
            <div class="flex justify-between"><dt class="text-navy/50">Guest count</dt><dd>{{ event.guest_count ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-navy/50">Budget</dt><dd>UGX {{ Number(event.budget || 0).toLocaleString() }}</dd></div>
            <div class="flex justify-between"><dt class="text-navy/50">Start / End</dt><dd>{{ event.start_time || '—' }} – {{ event.end_time || '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-navy/50">Type</dt><dd>{{ event.event_type?.name || '—' }}</dd></div>
          </dl>
          <p v-if="event.notes" class="text-sm text-navy/60 mt-4 border-t border-navy/5 pt-3">{{ event.notes }}</p>
        </div>
        <div class="card">
          <h3 class="font-bold mb-3">Services</h3>
          <div v-if="!event.services.length" class="text-sm text-navy/40">No services attached yet.</div>
          <ul v-else class="text-sm divide-y divide-navy/5">
            <li v-for="s in event.services" :key="s.id" class="py-2 flex justify-between">
              <span>{{ s.name }} × {{ s.pivot.quantity }}</span>
              <span class="font-semibold">UGX {{ Number(s.pivot.subtotal).toLocaleString() }}</span>
            </li>
          </ul>
          <button class="btn-outline w-full mt-4 !py-2 text-xs" @click="showServices = true">Manage Services</button>
        </div>
      </div>

      <!-- Staff & Vendors -->
      <div v-if="tab === 'Staff & Vendors'" class="grid md:grid-cols-2 gap-6 mt-6">
        <div class="card">
          <div class="flex justify-between items-center mb-3"><h3 class="font-bold">Assigned Staff</h3><button class="btn-outline !py-1 !px-2 text-xs" @click="showStaff = true">Manage</button></div>
          <div v-if="!event.staff.length" class="text-sm text-navy/40">No staff assigned.</div>
          <ul v-else class="text-sm divide-y divide-navy/5">
            <li v-for="s in event.staff" :key="s.id" class="py-2">{{ s.name }} <span class="text-navy/40">— {{ s.role_title }}</span></li>
          </ul>
        </div>
        <div class="card">
          <div class="flex justify-between items-center mb-3"><h3 class="font-bold">Vendors</h3><button class="btn-outline !py-1 !px-2 text-xs" @click="showVendors = true">Manage</button></div>
          <div v-if="!event.vendors.length" class="text-sm text-navy/40">No vendors assigned.</div>
          <ul v-else class="text-sm divide-y divide-navy/5">
            <li v-for="v in event.vendors" :key="v.id" class="py-2">{{ v.name }} <span class="text-navy/40">— {{ v.category }}</span></li>
          </ul>
        </div>
      </div>

      <!-- Tasks -->
      <div v-if="tab === 'Tasks'" class="card mt-6">
        <div class="flex justify-between items-center mb-4"><h3 class="font-bold">Tasks</h3><button class="btn-gold !py-1.5 !px-3 text-xs" @click="showTask = true">+ Add Task</button></div>
        <div v-if="!event.tasks.length" class="text-sm text-navy/40">No tasks yet.</div>
        <div v-for="t in event.tasks" :key="t.id" class="flex items-center justify-between py-2.5 border-t border-navy/5 first:border-0">
          <div>
            <p class="text-sm font-medium">{{ t.title }}</p>
            <p class="text-xs text-navy/40">{{ t.assigned_staff?.name || 'Unassigned' }} · Due {{ t.due_date ? formatDate(t.due_date) : '—' }}</p>
          </div>
          <select v-model="t.status" class="input !w-32 !py-1 !text-xs" @change="updateTask(t)">
            <option value="todo">To Do</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>
      </div>

      <!-- Payments -->
      <div v-if="tab === 'Payments'" class="card mt-6">
        <h3 class="font-bold mb-4">Payment History</h3>
        <div v-if="!event.payments.length" class="text-sm text-navy/40">No payments recorded.</div>
        <table v-else class="w-full text-sm">
          <tbody>
            <tr v-for="p in event.payments" :key="p.id" class="border-t border-navy/5 first:border-0">
              <td class="py-2.5 font-mono text-xs">{{ p.payment_reference }}</td>
              <td class="py-2.5 capitalize">{{ p.method.replace('_', ' ') }}</td>
              <td class="py-2.5">{{ formatDate(p.paid_at) }}</td>
              <td class="py-2.5 text-right font-semibold">UGX {{ Number(p.amount).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Messages -->
      <div v-if="tab === 'Messages'" class="card mt-6">
        <h3 class="font-bold mb-4">Event Communication</h3>
        <p class="text-xs text-navy/40 mb-4">Secure messaging — encryption architecture prepared for production implementation.</p>
        <div class="space-y-3 max-h-80 overflow-y-auto">
          <div v-for="m in messages" :key="m.id" class="text-sm bg-navy/[0.03] rounded-lg p-3">
            <p class="font-semibold text-xs text-navy/60">{{ m.sender?.name }}</p>
            <p class="mt-1">{{ m.body }}</p>
          </div>
        </div>
        <form class="flex gap-2 mt-4" @submit.prevent="sendMessage">
          <input v-model="newMessage" class="input" placeholder="Write a message…" />
          <button class="btn-primary !px-4">Send</button>
        </form>
      </div>
    </div>

    <!-- Manage services modal -->
    <div v-if="showServices" class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 backdrop-blur-sm px-4">
      <div class="card max-w-md w-full max-h-[85vh] overflow-y-auto">
        <h3 class="font-bold mb-4">Manage Services</h3>
        <div v-for="s in allServices" :key="s.id" class="flex items-center justify-between py-2 border-b border-navy/5">
          <label class="flex items-center gap-2 text-sm"><input type="checkbox" :value="s.id" v-model="serviceForm.selected" />{{ s.name }}</label>
          <input v-if="serviceForm.selected.includes(s.id)" type="number" min="1" v-model="serviceForm.qty[s.id]" class="input !w-16 !py-1 text-xs" />
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button class="btn-outline" @click="showServices = false">Cancel</button>
          <button class="btn-gold" @click="saveServices">Save</button>
        </div>
      </div>
    </div>

    <!-- Manage staff modal -->
    <div v-if="showStaff" class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 backdrop-blur-sm px-4">
      <div class="card max-w-md w-full max-h-[85vh] overflow-y-auto">
        <h3 class="font-bold mb-4">Assign Staff</h3>
        <label v-for="s in allStaff" :key="s.id" class="flex items-center gap-2 text-sm py-2 border-b border-navy/5">
          <input type="checkbox" :value="s.id" v-model="staffForm" />{{ s.name }} — {{ s.role_title }}
        </label>
        <div class="flex justify-end gap-3 mt-4">
          <button class="btn-outline" @click="showStaff = false">Cancel</button>
          <button class="btn-gold" @click="saveStaff">Save</button>
        </div>
      </div>
    </div>

    <!-- Manage vendors modal -->
    <div v-if="showVendors" class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 backdrop-blur-sm px-4">
      <div class="card max-w-md w-full max-h-[85vh] overflow-y-auto">
        <h3 class="font-bold mb-4">Assign Vendors</h3>
        <label v-for="v in allVendors" :key="v.id" class="flex items-center gap-2 text-sm py-2 border-b border-navy/5">
          <input type="checkbox" :value="v.id" v-model="vendorForm" />{{ v.name }} — {{ v.category }}
        </label>
        <div class="flex justify-end gap-3 mt-4">
          <button class="btn-outline" @click="showVendors = false">Cancel</button>
          <button class="btn-gold" @click="saveVendors">Save</button>
        </div>
      </div>
    </div>

    <!-- Add task modal -->
    <div v-if="showTask" class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 backdrop-blur-sm px-4">
      <div class="card max-w-md w-full">
        <h3 class="font-bold mb-4">Add Task</h3>
        <form class="space-y-4" @submit.prevent="createTask">
          <div><label class="label">Title</label><input v-model="taskForm.title" class="input" required /></div>
          <div><label class="label">Assign to</label>
            <select v-model="taskForm.assigned_staff_id" class="input">
              <option value="">Unassigned</option>
              <option v-for="s in allStaff" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="label">Due Date</label><input v-model="taskForm.due_date" type="date" class="input" /></div>
            <div><label class="label">Priority</label>
              <select v-model="taskForm.priority" class="input"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option></select>
            </div>
          </div>
          <div class="flex justify-end gap-3"><button type="button" class="btn-outline" @click="showTask = false">Cancel</button><button class="btn-gold">Add Task</button></div>
        </form>
      </div>
    </div>

    <Toast :message="toast.state.message" :type="toast.state.type" />
  </AdminShell>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api'
import AdminShell from '@/components/AdminShell.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const toast = useToast()

const event = ref(null)
const messages = ref([])
const loading = ref(true)
const tab = ref('Overview')
const tabs = ['Overview', 'Staff & Vendors', 'Tasks', 'Payments', 'Messages']
const statuses = ['enquiry', 'quotation', 'deposit', 'confirmed', 'planning', 'ready', 'completed', 'cancelled']

const showServices = ref(false)
const showStaff = ref(false)
const showVendors = ref(false)
const showTask = ref(false)
const allServices = ref([])
const allStaff = ref([])
const allVendors = ref([])
const serviceForm = reactive({ selected: [], qty: {} })
const staffForm = ref([])
const vendorForm = ref([])
const taskForm = ref({ title: '', assigned_staff_id: '', due_date: '', priority: 'medium' })
const newMessage = ref('')

function formatDate(d) { return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }

async function load() {
  loading.value = true
  const { data } = await api.get(`/events/${route.params.id}`)
  event.value = data
  const msgs = await api.get(`/events/${route.params.id}/messages`)
  messages.value = msgs.data
  loading.value = false
}

async function updateStatus() {
  try {
    await api.put(`/events/${event.value.id}`, { status: event.value.status })
    toast.show('Event status updated.')
  } catch (e) {
    toast.show('Unable to update status.', 'error')
  }
}

async function ensureLookups() {
  if (!allServices.value.length) allServices.value = (await api.get('/services')).data
  if (!allStaff.value.length) allStaff.value = (await api.get('/staff')).data
  if (!allVendors.value.length) allVendors.value = (await api.get('/vendors')).data
}

async function saveServices() {
  const payload = serviceForm.selected.map((id) => ({ service_id: id, quantity: Number(serviceForm.qty[id] || 1) }))
  await api.post(`/events/${event.value.id}/services`, { services: payload })
  showServices.value = false
  toast.show('Services updated.')
  await load()
}

async function saveStaff() {
  await api.post(`/events/${event.value.id}/staff`, { staff_ids: staffForm.value })
  showStaff.value = false
  toast.show('Staff assignment updated.')
  await load()
}

async function saveVendors() {
  await api.post(`/events/${event.value.id}/vendors`, { vendor_ids: vendorForm.value })
  showVendors.value = false
  toast.show('Vendors updated.')
  await load()
}

async function createTask() {
  await api.post('/tasks', { ...taskForm.value, event_id: event.value.id })
  showTask.value = false
  taskForm.value = { title: '', assigned_staff_id: '', due_date: '', priority: 'medium' }
  toast.show('Task added.')
  await load()
}

async function updateTask(t) {
  await api.put(`/tasks/${t.id}`, { status: t.status })
  toast.show('Task status updated.')
}

async function sendMessage() {
  if (!newMessage.value.trim()) return
  const { data } = await api.post(`/events/${event.value.id}/messages`, { body: newMessage.value })
  messages.value.push(data)
  newMessage.value = ''
}

onMounted(async () => {
  await load()
  await ensureLookups()
  staffForm.value = event.value.staff.map((s) => s.id)
  vendorForm.value = event.value.vendors.map((v) => v.id)
  serviceForm.selected = event.value.services.map((s) => s.id)
  event.value.services.forEach((s) => { serviceForm.qty[s.id] = s.pivot.quantity })
})
</script>
