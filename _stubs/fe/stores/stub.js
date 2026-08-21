import { defineStore } from 'pinia'
import { {{MODULES_LOWER}}Api } from '@/modules/{{MODULES_LOWER}}/services/{{MODULES_LOWER}}Api'

export const use{{MODULES}}Store = defineStore('{{MODULES_LOWER}}', {
  state: () => ({ current: null, loading: false }),
  actions: {
    async fetchOne(id) {
      this.loading = true
      try {
        const res = await {{MODULES_LOWER}}Api.show(id)
        this.current = res.data ?? res
      } finally {
        this.loading = false
      }
    },
    async create(data) {
      return {{MODULES_LOWER}}Api.create(data)
    },
    async update(id, data) {
      const res = await {{MODULES_LOWER}}Api.update(id, data)
      this.current = res.data ?? this.current
      return res
    },
    async remove(id) {
      return {{MODULES_LOWER}}Api.remove(id)
    },
  },
})
