import { ref } from 'vue'
import api from '@/shared/services/api'
import { useToastStore } from '@/shared/stores/toast'

export function useDetailFetcher(endpoint) {
  const data = ref({})
  const loading = ref(true)

  async function init() {
    try {
      const res = await api.get(endpoint)
      data.value = res.data ?? res
    } catch (err) {
      useToastStore().catch(err)
    } finally {
      loading.value = false
    }
  }

  return { data, loading, init }
}
