<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useSidebar } from '@/composables/useSidebar'
import Sidebar from '@/components/layout/Sidebar.vue'
import Navbar from '@/components/layout/Navbar.vue'

const { sidebarOpen, close } = useSidebar()

function onKeydown(e) {
  if (e.key === 'Escape') close()
}
onMounted(() => window.addEventListener('keydown', onKeydown))
onUnmounted(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
  <div class="flex min-h-screen">
    <div
      v-show="sidebarOpen"
      class="fixed inset-0 z-30 bg-black/50 lg:hidden"
      aria-hidden="true"
      @click="close()"
    ></div>

    <Sidebar />

    <div class="flex flex-col flex-1 min-w-0">
      <Navbar />

      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
