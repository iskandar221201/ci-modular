<script setup>
defineProps({
  visible: { type: Boolean, default: false },
  message: { type: String, default: '' },
})
const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <div
    v-show="visible"
    class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirmDialogTitle"
    aria-describedby="confirmDialogMessage"
  >
    <div v-show="visible" class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" @click.stop>
      <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mx-auto mb-4">
        <svg
          class="w-6 h-6 text-red-600"
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
      </div>

      <h3 id="confirmDialogTitle" class="text-lg font-semibold text-gray-900 text-center mb-2">
        Konfirmasi Tindakan
      </h3>

      <p id="confirmDialogMessage" class="text-sm text-gray-600 text-center mb-6">{{ message }}</p>

      <div class="flex flex-col-reverse sm:flex-row items-center justify-center gap-3">
        <button
          type="button"
          class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 transition-colors"
          @click="emit('cancel')"
        >
          Batal
        </button>

        <button
          type="button"
          class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors"
          @click="emit('confirm')"
        >
          Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</template>
