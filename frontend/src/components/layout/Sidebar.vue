<script setup>
import { useSidebar } from '@/composables/useSidebar'

const appName = import.meta.env.VITE_APP_NAME || 'CI4 Kit'
const appVersion = import.meta.env.VITE_APP_VERSION || '1.0.0'
const { sidebarOpen } = useSidebar()

const navItems = [
  {
    label: 'Dashboard',
    to: '/dashboard',
    d: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
  },
  {
    label: 'Pengguna',
    to: '/users',
    d: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
  },
  {
    label: 'Showcase',
    to: '/showcase',
    d: 'M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42',
  },
]
</script>

<template>
  <aside
    class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:z-auto lg:flex-shrink-0 lg:min-h-screen"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  >
    <div class="flex items-center h-16 px-6 border-b border-gray-200">
      <a href="/" class="text-gray-900 font-bold text-xl tracking-tight">{{ appName }}</a>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <router-link
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        active-class="bg-gray-900 text-white"
        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors text-gray-600 hover:bg-gray-100 hover:text-gray-900"
      >
        <svg
          class="w-5 h-5 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path stroke-linecap="round" stroke-linejoin="round" :d="item.d" />
        </svg>
        <span>{{ item.label }}</span>
      </router-link>
    </nav>

    <div class="px-6 py-4 border-t border-gray-200">
      <span class="text-xs text-gray-400">v{{ appVersion }}</span>
    </div>
  </aside>
</template>
