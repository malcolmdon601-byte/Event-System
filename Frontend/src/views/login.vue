<template>
  <div class="min-h-screen flex items-center justify-center bg-navy px-4">
    <div class="w-full max-w-md">
      <router-link to="/" class="flex justify-center items-center gap-2 font-extrabold text-2xl text-white mb-8">
        <span class="text-gold">Event</span>Flow
      </router-link>

      <div class="card">
        <h1 class="font-bold text-xl">Sign in</h1>
        <p class="text-sm text-navy/50 mt-1">Access your dashboard or customer portal.</p>

        <form class="mt-6 space-y-4" @submit.prevent="submit">
          <div>
            <label for="login-email" class="label">Email</label>
            <input id="login-email" v-model="email" type="email" class="input" autocomplete="username" required />
          </div>
          <div>
            <label for="login-password" class="label">Password</label>
            <div class="relative">
              <input id="login-password" v-model="password" :type="showPassword ? 'text' : 'password'" class="input pr-20" autocomplete="current-password" required />
              <button type="button" class="absolute inset-y-0 right-3 my-auto h-8 px-2 text-xs font-semibold text-navy/60 hover:text-navy" :aria-label="showPassword ? 'Hide password' : 'Show password'" :aria-pressed="showPassword" @click="showPassword = !showPassword">
                {{ showPassword ? 'Hide' : 'Show' }}
              </button>
            </div>
          </div>
          <p v-if="error" class="text-sm text-danger">{{ error }}</p>
          <button class="btn-gold w-full" :disabled="loading">{{ loading ? 'Signing in…' : 'Sign In' }}</button>
        </form>

        <div class="mt-6 pt-6 border-t border-navy/10">
          <p class="text-xs font-semibold uppercase tracking-wide text-navy/40 mb-2">Demo accounts (password: password)</p>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <button v-for="d in demoAccounts" :key="d.email" class="btn-outline !py-1.5 !px-2 text-left" @click="fill(d.email)">
              {{ d.label }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const email = ref('')
const password = ref('password')
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const demoAccounts = [
  { label: 'Super Admin', email: 'admin@eventflow.test' },
  { label: 'Manager', email: 'manager@eventflow.test' },
  { label: 'Finance', email: 'finance@eventflow.test' },
  { label: 'Event Staff', email: 'staff@eventflow.test' },
  { label: 'Customer', email: 'customer@eventflow.test' },
]

function fill(e) { email.value = e }

async function submit() {
  loading.value = true
  error.value = ''
  try {
    const user = await auth.login(email.value, password.value)
    const redirect = route.query.redirect
    if (redirect) router.push(redirect)
    else router.push(user.role === 'customer' ? '/portal' : '/admin')
  } catch (e) {
    error.value = e.response?.data?.message || 'Unable to sign in. Please check your details.'
  } finally {
    loading.value = false
  }
}
</script>
