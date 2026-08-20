import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/modules/auth/stores/auth'

export function useAuth() {
  const store = useAuthStore()
  const { user, fetchedMe, isAuthenticated, username } = storeToRefs(store)

  return {
    user,
    fetchedMe,
    isAuthenticated,
    username,
    login: store.login,
    fetchMe: store.fetchMe,
    logout: store.logout,
  }
}