import { defineStore } from 'pinia'
import { usersApi } from '@/modules/users/services/usersApi'

export const useUsersStore = defineStore('users', {
  state: () => ({ current: null, loading: false }),
  actions: {
    async fetchOne(id) {
      this.loading = true
      try {
        const res = await usersApi.show(id)
        this.current = res.data ?? res
      } finally {
        this.loading = false
      }
    },
    async create(data) {
      return usersApi.create(data)
    },
    async update(id, data) {
      const res = await usersApi.update(id, data)
      this.current = res.data ?? this.current
      return res
    },
    async remove(id) {
      return usersApi.remove(id)
    },
  },
})