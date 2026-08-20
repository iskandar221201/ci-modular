<script setup>
defineProps({
  title: { type: String, default: '' },
  fields: { type: Array, default: () => [] },
})
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div v-if="title" class="px-6 py-4 border-b border-gray-100">
      <h2 class="text-base font-semibold text-gray-900">{{ title }}</h2>
    </div>

    <dl class="divide-y divide-gray-100">
      <div
        v-for="(field, i) in fields"
        :key="i"
        class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-4"
      >
        <dt class="text-sm font-medium text-gray-500 flex items-start">{{ field.label }}</dt>
        <dd class="text-sm text-gray-900 col-span-1 sm:col-span-2 break-words">
          <span v-if="field.value === null || field.value === ''" class="text-gray-400 italic"
            >—</span
          >
          <template v-else>{{ field.value }}</template>
        </dd>
      </div>

      <div v-if="fields.length === 0" class="px-6 py-4 text-sm text-gray-400 italic">
        Tidak ada data.
      </div>
    </dl>
  </div>
</template>
