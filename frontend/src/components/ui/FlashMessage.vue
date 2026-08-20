<script setup>
import { ref } from 'vue'

defineProps({
  type: { type: String, default: 'success' },
  message: { type: String, required: true },
})

const show = ref(true)
</script>

<template>
  <div
    v-show="show"
    class="flex items-start gap-3 p-4 rounded-lg border"
    :class="
      type === 'success'
        ? 'border-green-200 bg-green-50 text-green-800'
        : 'border-red-200 bg-red-50 text-red-800'
    "
    role="alert"
  >
    <svg
      v-if="type === 'success'"
      class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5"
      fill="none"
      stroke="currentColor"
      stroke-width="1.5"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
      />
    </svg>
    <svg
      v-else
      class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"
      fill="none"
      stroke="currentColor"
      stroke-width="1.5"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
      />
    </svg>

    <div class="flex-1">
      <p class="text-sm font-medium">{{ message }}</p>
    </div>

    <button
      type="button"
      class="text-gray-500 hover:text-gray-700 focus:outline-none"
      aria-label="Tutup notifikasi"
      @click="show = false"
    >
      <svg
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        viewBox="0 0 24 24"
        aria-hidden="true"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</template>
