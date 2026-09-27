import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { path: '/', name: 'home', component: () => import('@/views/public/Home.vue') },
  { path: '/services', name: 'services', component: () => import('@/views/public/Services.vue') },
  { path: '/request-quote', name: 'request-quote', component: () => import('@/views/public/RequestQuote.vue') },
  { path: '/login', name: 'login', component: () => import('@/views/login.vue') },

  { path: '/portal', name: 'portal', component: () => import('@/views/portal/Portal.vue'),
    meta: { requiresAuth: true, roles: ['customer'] } },

  { path: '/admin', name: 'admin-dashboard', component: () => import('@/views/admin/Dashboard.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'manager', 'finance', 'staff'] } },
  { path: '/admin/events', name: 'admin-events', component: () => import('@/views/admin/Events.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'manager', 'finance', 'staff'] } },
  { path: '/admin/events/:id', name: 'admin-event-detail', component: () => import('@/views/admin/EventDetail.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'manager', 'finance', 'staff'] } },
  { path: '/admin/customers', name: 'admin-customers', component: () => import('@/views/admin/Customers.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'manager', 'finance', 'staff'] } },
  { path: '/admin/quotations', name: 'admin-quotations', component: () => import('@/views/admin/Quotations.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'manager', 'finance'] } },
  { path: '/admin/payments', name: 'admin-payments', component: () => import('@/views/admin/Payments.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'manager', 'finance'] } },
  { path: '/admin/reports', name: 'admin-reports', component: () => import('@/views/admin/Reports.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'manager', 'finance'] } },

  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.roles && auth.isAuthenticated && !to.meta.roles.includes(auth.user?.role)) {
    return { name: auth.isCustomer ? 'portal' : 'admin-dashboard' }
  }
})

export default router
