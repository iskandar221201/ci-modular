<script setup>
defineProps({
  visible: { type: Boolean, default: false },
})
</script>

<template>
  <div
    v-show="visible"
    class="fixed inset-0 bg-white bg-opacity-75 z-50 flex items-center justify-center"
    role="status"
    aria-label="Memuat..."
  >
    <div class="flex flex-col items-center gap-3">
      <svg
        class="w-10 h-10 text-gray-900 animate-spin"
        fill="none"
        viewBox="0 0 24 24"
        aria-hidden="true"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        ></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>
      <span class="text-sm font-medium text-gray-600">Memuat...</span>
    </div>
  </div>
</template>
