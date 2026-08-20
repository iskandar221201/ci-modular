import { ref } from 'vue'
import api from '@/shared/services/api'
import { useToastStore } from '@/shared/stores/toast'

export function useDataTable(endpoint, perPage = 10) {
  const data = ref([])
  const meta = ref({})
  const loading = ref(true)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const search = ref('')

  let debounceTimer = null

  async function fetch() {
    loading.value = true
    try {
      const params = new URLSearchParams({
        page: currentPage.value,
        search: search.value,
        per_page: perPage,
      })
      const res = await api.get(`${endpoint}?${params}`)
      data.value = res.data ?? []
      meta.value = res.meta ?? {}
      totalPages.value = res.meta?.total_pages ?? 1
    } catch (err) {
      useToastStore().catch(err)
      data.value = []
      meta.value = {}
    } finally {
      loading.value = false
    }
  }

  function onSearch() {
    currentPage.value = 1
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(fetch, 400)
  }

  async function changePage(page) {
    if (page < 1 || page > totalPages.value) return
    currentPage.value = page
    await fetch()
  }

  async function init() {
    await fetch()
  }

  return { data, meta, loading, currentPage, totalPages, search, fetch, onSearch, changePage, init }
}
