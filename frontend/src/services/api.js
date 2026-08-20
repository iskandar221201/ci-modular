import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  withCredentials: true,
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

api.interceptors.response.use(
  (res) => {
    if (res.status === 204) return { status: true, data: null }
    if (res.data && res.data.status === true) return res.data
    return Promise.reject({ message: res.data?.message || 'Unexpected response' })
  },
  (err) => {
    const status = err.response?.status
    const data = err.response?.data
    const url = err.config?.url || ''
    const toast = useToastStore()
    const auth = useAuthStore()

    if (status === 401) {
      if (!url.includes('/auth/login')) {
        auth.user = null
        // ponytail: skip hard redirect during bootstrap; the router guard handles it
        if (auth.fetchedMe) window.location.href = '/login'
      }
      return Promise.reject({ message: data?.message || 'Email atau password salah.' })
    }
    if (status === 422) {
      return Promise.reject({ errors: data?.errors })
    }
    if (err.request && !err.response) {
      toast.catch({ message: 'Unable to connect to the server.' })
      return Promise.reject(err)
    }
    toast.catch({ message: data?.message || 'Something went wrong.' })
    return Promise.reject({ message: data?.message })
  },
)

export default api
