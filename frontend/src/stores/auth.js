import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({ user: null, fetchedMe: false }),
  getters: {
    isAuthenticated: (s) => s.user !== null,
    username: (s) => s.user?.username || '',
  },
  actions: {
    async login({ email, password }) {
      const res = await api.post('/auth/login', { email, password })
      this.user = { id: res.data.id, username: res.data.username, email: res.data.email }
      return res
    },
    async fetchMe() {
      try {
        const res = await api.get('/auth/me')
        this.user = res.data
      } catch (_e) {
        this.user = null
      } finally {
        this.fetchedMe = true
      }
    },
    async logout() {
      try {
        // ponytail: empty body so axios keeps Content-Type: application/json (JsonBodyFilter)
        await api.post('/auth/logout', {})
      } catch (_e) {
        /* cookie cleared server-side regardless */
      }
      this.user = null
    },
  },
})
