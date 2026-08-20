<script setup>
defineProps({
  isSubmitting: { type: Boolean, default: false },
  submitLabel: { type: String, default: 'Simpan' },
  submitLoadingLabel: { type: String, default: 'Menyimpan...' },
  cancelLabel: { type: String, default: 'Batal' },
  cancelUrl: { type: String, default: null },
})
const emit = defineEmits(['cancel'])
</script>

<template>
  <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-1">
    <button
      type="submit"
      :disabled="isSubmitting"
      class="px-5 py-2.5 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <span v-show="isSubmitting">{{ submitLoadingLabel }}</span>
      <span v-show="!isSubmitting">{{ submitLabel }}</span>
    </button>

    <router-link
      v-if="cancelUrl"
      :to="cancelUrl"
      class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
    >
      {{ cancelLabel }}
    </router-link>
    <button
      v-else
      type="button"
      class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
      @click="emit('cancel')"
    >
      {{ cancelLabel }}
    </button>
  </div>
</template>
