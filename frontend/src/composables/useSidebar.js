import { ref } from 'vue'

const sidebarOpen = ref(false)

export function useSidebar() {
  function toggle() {
    sidebarOpen.value = !sidebarOpen.value
  }
  function close() {
    sidebarOpen.value = false
  }
  return { sidebarOpen, toggle, close }
}
