<template>
  <div>
    <PublicNav />
    <section class="max-w-3xl mx-auto px-4 sm:px-6 py-10 md:py-16">
      <h1 class="text-3xl font-extrabold">Request a Quote</h1>
      <p class="text-navy/50 mt-2">Tell us about your event and we'll get back to you with a tailored quotation.</p>

      <div v-if="submitted" class="card mt-8 border-success/30 bg-success/5">
        <h3 class="font-bold text-success">Thank you, {{ form.name }}!</h3>
        <p class="text-sm text-navy/60 mt-2">{{ successMessage }}</p>
        <p class="text-xs text-navy/40 mt-1">Reference: {{ reference }}</p>
      </div>

      <form v-else class="card mt-8 space-y-5" @submit.prevent="submit">
        <div class="grid md:grid-cols-2 gap-5">
          <div>
            <label for="enquiry-name" class="label">Full Name</label>
            <input id="enquiry-name" v-model="form.name" class="input" autocomplete="name" required :aria-invalid="!!fieldErrors.name" />
            <p v-if="fieldErrors.name" class="mt-1 text-sm text-danger">{{ fieldErrors.name }}</p>
          </div>
          <div>
            <label for="enquiry-email" class="label">Email</label>
            <input id="enquiry-email" v-model="form.email" type="email" class="input" autocomplete="email" required :aria-invalid="!!fieldErrors.email" />
            <p v-if="fieldErrors.email" class="mt-1 text-sm text-danger">{{ fieldErrors.email }}</p>
          </div>
          <div>
            <label for="enquiry-phone" class="label">Phone</label>
            <input id="enquiry-phone" v-model="form.phone" class="input" type="tel" autocomplete="tel" />
          </div>
          <div>
            <label class="label">Event Type</label>
            <select v-model="form.event_type_id" class="input">
              <option value="">Select a type</option>
              <option v-for="t in eventTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Event Date</label>
            <input v-model="form.event_date" type="date" class="input" :min="minEventDate" required :aria-invalid="!!fieldErrors.event_date" />
            <p v-if="fieldErrors.event_date" class="mt-1 text-sm text-danger">{{ fieldErrors.event_date }}</p>
          </div>
          <div>
            <label class="label">Guest Count</label>
            <input v-model="form.guest_count" type="number" min="1" class="input" />
          </div>
          <div>
            <label class="label">Venue (if known)</label>
            <input v-model="form.venue" class="input" />
          </div>
          <div>
            <label class="label">Estimated Budget (UGX)</label>
            <input v-model="form.budget" type="number" min="0" class="input" />
          </div>
        </div>

        <div>
          <label class="label">Services of interest</label>
          <div class="grid md:grid-cols-2 gap-2">
            <label v-for="s in services" :key="s.id" class="flex min-h-11 items-center gap-2 text-sm border border-navy/10 rounded-lg px-3 py-2">
              <input type="checkbox" :value="s.id" v-model="form.service_ids" />
              {{ s.name }}
            </label>
          </div>
        </div>

        <div>
          <label class="label">Tell us more</label>
          <textarea v-model="form.message" rows="4" class="input" placeholder="Vision, must-haves, anything we should know…"></textarea>
        </div>

        <div v-if="optionsError" class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900" role="status">
          {{ optionsError }}
        </div>

        <div v-if="error" class="rounded-lg bg-red-50 p-3 text-sm text-danger" role="alert">
          <p>{{ error }}</p>
          <ul v-if="Object.keys(fieldErrors).length" class="mt-1 list-inside list-disc">
            <li v-for="(message, field) in fieldErrors" :key="field">{{ message }}</li>
          </ul>
        </div>

        <button class="btn-gold w-full" type="submit" :disabled="submitting" :aria-busy="submitting">{{ submitting ? 'Sending…' : 'Submit Enquiry' }}</button>
      </form>
    </section>
    <PublicFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/api'
import PublicNav from '@/components/PublicNav.vue'
import PublicFooter from '@/components/PublicFooter.vue'

const eventTypes = ref([])
const services = ref([])
const submitting = ref(false)
const submitted = ref(false)
const successMessage = ref('')
const reference = ref('')
const error = ref('')
const fieldErrors = ref({})
const optionsError = ref('')
const minEventDate = computed(() => {
  const today = new Date()
  today.setMinutes(today.getMinutes() - today.getTimezoneOffset())
  return today.toISOString().slice(0, 10)
})

const form = ref({
  name: '', email: '', phone: '', event_type_id: '', event_date: '', guest_count: '',
  venue: '', budget: '', message: '', service_ids: [],
})

onMounted(async () => {
  try {
    const [types, svcs] = await Promise.all([
      api.get('/public/event-types'),
      api.get('/public/services'),
    ])
    eventTypes.value = types.data
    services.value = svcs.data
  } catch {
    optionsError.value = 'Event options could not be loaded. You can still submit your enquiry without selecting them.'
  }
})

async function submit() {
  submitting.value = true
  error.value = ''
  fieldErrors.value = {}
  try {
    const { data } = await api.post('/public/request-quote', form.value)
    successMessage.value = data.message
    reference.value = data.reference
    submitted.value = true
  } catch (e) {
    error.value = e.response?.data?.message || 'Unable to submit your enquiry. Please check the highlighted fields.'
    fieldErrors.value = Object.fromEntries(
      Object.entries(e.response?.data?.errors || {}).map(([field, messages]) => [field, messages[0]])
    )
  } finally {
    submitting.value = false
  }
}
</script>
