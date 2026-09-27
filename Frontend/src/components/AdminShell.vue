<template>
  <div class="min-h-screen flex bg-bg">
    <aside class="w-64 shrink-0 bg-navy text-white flex flex-col fixed inset-y-0" :class="mobileOpen ? 'flex' : 'hidden md:flex'">
      <div class="h-16 flex items-center px-6 font-extrabold text-lg tracking-tight border-b border-white/10">
        <span class="text-gold">Event</span>Flow
      </div>
      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
        <router-link v-for="item in navItems" :key="item.to" :to="item.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition"
          :class="isActive(item.to) ? 'bg-gold text-navy' : 'text-white/70 hover:bg-white/10 hover:text-white'">
          <span class="w-5 text-center">{{ item.icon }}</span>{{ item.label }}
        </router-link>
      </nav>
      <div class="p-4 border-t border-white/10">
        <p class="text-sm font-semibold">{{ auth.user?.name }}</p>
        <p class="text-xs text-white/50 capitalize">{{ auth.user?.role?.replace('_', ' ') }}</p>
        <button class="btn-outline !border-white/20 !text-white hover:!bg-white/10 w-full mt-3 text-xs !py-2" @click="logout">
          Sign Out
        </button>
      </div>
    </aside>

    <div class="flex-1 md:ml-64">
      <header class="h-16 bg-white border-b border-navy/5 flex items-center justify-between px-6 sticky top-0 z-30">
        <button class="md:hidden btn-outline !px-3 !py-1.5" @click="mobileOpen = !mobileOpen">☰</button>
        <h1 class="font-bold text-lg">{{ title }}</h1>
        <div class="w-8 h-8 rounded-full bg-champagne text-navy flex items-center justify-center text-xs font-bold">
          {{ initials }}
        </div>
      </header>
      <main class="p-6 max-w-7xl mx-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps({ title: { type: String, default: 'Dashboard' } })

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const mobileOpen = ref(false)

const allNavItems = [
  { to: '/admin', label: 'Dashboard', icon: '◆', roles: ['super_admin', 'manager', 'finance', 'staff'] },
  { to: '/admin/events', label: 'Events', icon: '▤', roles: ['super_admin', 'manager', 'finance', 'staff'] },
  { to: '/admin/customers', label: 'Customers', icon: '☺', roles: ['super_admin', 'manager', 'finance', 'staff'] },
  { to: '/admin/quotations', label: 'Quotations', icon: '¤', roles: ['super_admin', 'manager', 'finance'] },
  { to: '/admin/payments', label: 'Payments', icon: '$', roles: ['super_admin', 'manager', 'finance'] },
  { to: '/admin/reports', label: 'Reports', icon: '▦', roles: ['super_admin', 'manager', 'finance'] },
]

const navItems = computed(() => allNavItems.filter((i) => i.roles.includes(auth.user?.role)))

function isActive(to) {
  return to === '/admin' ? route.path === '/admin' : route.path.startsWith(to)
}

const initials = computed(() => (auth.user?.name || '?').split(' ').map((n) => n[0]).slice(0, 2).join('').toUpperCase())

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>
