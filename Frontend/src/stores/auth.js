import { defineStore } from 'pinia'
import api from '@/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('ef_token') || null,
    user: JSON.parse(localStorage.getItem('ef_user') || 'null'),
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdminRole: (state) => ['super_admin', 'manager', 'finance', 'staff'].includes(state.user?.role),
    isCustomer: (state) => state.user?.role === 'customer',
  },
  actions: {
    async login(email, password) {
      const { data } = await api.post('/auth/login', { email, password })
      this.setSession(data.token, data.user)
      return data.user
    },
    async register(payload) {
      const { data } = await api.post('/auth/register', payload)
      this.setSession(data.token, data.user)
      return data.user
    },
    setSession(token, user) {
      this.token = token
      this.user = user
      localStorage.setItem('ef_token', token)
      localStorage.setItem('ef_user', JSON.stringify(user))
    },
    async logout() {
      try { await api.post('/auth/logout') } catch (e) { /* ignore */ }
      this.token = null
      this.user = null
      localStorage.removeItem('ef_token')
      localStorage.removeItem('ef_user')
    },
  },
})
