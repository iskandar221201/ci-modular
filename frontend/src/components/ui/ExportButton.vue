<script setup>
import { ref } from 'vue'
import { usePdfExport } from '@/composables/usePdfExport'

const props = defineProps({
  endpoint: { type: String, required: true },
  label: { type: String, default: 'Export PDF' },
  params: { type: Object, default: () => ({}) },
})

const isExporting = ref(false)

async function exportPdf() {
  isExporting.value = true
  await usePdfExport(props.endpoint, props.params)
  isExporting.value = false
}
</script>

<template>
  <button
    type="button"
    :disabled="isExporting"
    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
    @click="exportPdf()"
  >
    <svg
      v-show="isExporting"
      class="w-4 h-4 animate-spin text-gray-500"
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

    <svg
      v-show="!isExporting"
      class="w-4 h-4 text-gray-500"
      fill="none"
      stroke="currentColor"
      stroke-width="1.5"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"
      />
    </svg>

    <span v-show="isExporting">Mengunduh...</span>
    <span v-show="!isExporting">{{ label }}</span>
  </button>
</template>
