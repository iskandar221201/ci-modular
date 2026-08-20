<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '@/modules/auth/composables/useAuth'
import { useSidebar } from '@/shared/composables/useSidebar'

const router = useRouter()
const auth = useAuth()
const { toggle } = useSidebar()

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <header
    class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0"
  >
    <div class="flex items-center gap-2">
      <button
        type="button"
        class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
        aria-label="Open navigation menu"
        @click="toggle()"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
          />
        </svg>
      </button>
    </div>

    <div class="flex items-center gap-4">
      <span class="text-sm text-gray-700 font-medium">{{ auth.username }}</span>

      <button
        type="button"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500"
        aria-label="Sign out of the application"
        @click="logout()"
      >
        <svg
          class="w-4 h-4"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"
          />
        </svg>
        Keluar
      </button>
    </div>
  </header>
</template>
