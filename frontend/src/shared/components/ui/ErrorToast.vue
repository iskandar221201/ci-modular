<script setup>
import { useToastStore } from '@/shared/stores/toast'

const toast = useToastStore()
</script>

<template>
  <div
    v-show="toast.visible"
    class="fixed bottom-4 right-4 left-4 sm:left-auto z-50 flex items-center gap-3 text-white px-4 py-3 rounded-lg shadow-lg max-w-sm w-full"
    :class="{
      'bg-red-600': toast.type === 'error',
      'bg-yellow-500': toast.type === 'warning',
      'bg-gray-800': toast.type === 'info',
    }"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
  >
    <div class="flex-shrink-0">
      <svg
        v-show="toast.type === 'error'"
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
          d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
        />
      </svg>

      <svg
        v-show="toast.type === 'warning'"
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
          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
        />
      </svg>

      <svg
        v-show="toast.type === 'info'"
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
          d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"
        />
      </svg>
    </div>

    <span class="flex-1 text-sm font-medium">{{ toast.message }}</span>

    <button
      type="button"
      class="flex-shrink-0 opacity-75 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-white rounded"
      aria-label="Tutup notifikasi"
      @click="toast.visible = false"
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
