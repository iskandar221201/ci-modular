<script setup>
defineProps({
  message: { type: String, default: 'Tidak ada data.' },
  cta: { type: Object, default: null },
})
</script>

<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <svg
      class="w-20 h-20 text-gray-300 mb-4"
      fill="none"
      stroke="currentColor"
      stroke-width="1"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25-2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"
      />
    </svg>

    <p class="text-gray-500 text-sm font-medium mb-4">{{ message }}</p>

    <router-link
      v-if="cta"
      :to="cta.url"
      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 focus:outline-none transition-colors"
    >
      <svg
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        viewBox="0 0 24 24"
        aria-hidden="true"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
      {{ cta.label }}
    </router-link>
  </div>
</template>
