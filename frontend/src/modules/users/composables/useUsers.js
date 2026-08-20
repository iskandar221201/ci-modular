import { useUsersStore } from '@/modules/users/stores/users'

// Return the reactive store itself. Returning { current, loading } from
// storeToRefs breaks template unwrapping: `users.loading` inside a plain
// object is never unwrapped, so `v-show="users.loading"` stays truthy forever.
export function useUsers() {
  return useUsersStore()
}