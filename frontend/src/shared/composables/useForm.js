import { ref } from 'vue'
import api from '@/shared/services/api'
import router from '@/router'

export function useForm(endpoint, method = 'POST', redirectUrl = null) {
  const errors = ref({})
  const isSubmitting = ref(false)

  async function submit(data) {
    isSubmitting.value = true
    errors.value = {}
    try {
      await api.request({ method, url: endpoint, data })
      if (redirectUrl) await router.push(redirectUrl)
      return true
    } catch (err) {
      if (err && err.errors) errors.value = err.errors
      return false
    } finally {
      isSubmitting.value = false
    }
  }

  return { errors, isSubmitting, submit }
}
