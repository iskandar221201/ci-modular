<script setup>
import EmptyState from '@/components/ui/EmptyState.vue'
import Skeleton from '@/components/ui/Skeleton.vue'

defineProps({
  columns: { type: Array, required: true },
  actions: { type: Array, default: () => [] },
  data: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  skeletonRows: { type: Number, default: 5 },
  currentPage: { type: Number, default: 1 },
  totalPages: { type: Number, default: 1 },
})
const emit = defineEmits(['change-page'])
</script>

<template>
  <div v-show="loading" class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            scope="col"
            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap"
          >
            {{ col.label }}
          </th>
          <th
            v-if="actions.length"
            scope="col"
            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap"
          >
            Aksi
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        <tr v-for="i in skeletonRows" :key="i">
          <td v-for="col in columns" :key="col.key" class="px-4 py-3">
            <Skeleton height="0.875rem" :width="['70%', '85%', '60%'][i % 3]" />
          </td>
          <td v-if="actions.length" class="px-4 py-3">
            <Skeleton height="0.875rem" width="4rem" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div v-show="!loading && data.length === 0" class="mt-4">
    <EmptyState />
  </div>

  <div
    v-show="!loading && data.length > 0"
    class="mt-4 overflow-x-auto rounded-lg border border-gray-200"
  >
    <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            scope="col"
            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap"
          >
            {{ col.label }}
          </th>
          <th
            v-if="actions.length"
            scope="col"
            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap"
          >
            Aksi
          </th>
        </tr>
      </thead>

      <tbody class="bg-white divide-y divide-gray-100">
        <tr
          v-for="row in data"
          :key="row.id ?? JSON.stringify(row)"
          class="hover:bg-gray-50 transition-colors"
        >
          <td
            v-for="col in columns"
            :key="col.key"
            class="px-4 py-3 text-gray-700 whitespace-nowrap"
          >
            {{ row[col.key] ?? '-' }}
          </td>
          <td v-if="actions.length" class="px-4 py-3 whitespace-nowrap">
            <span class="inline-flex items-center gap-3">
              <router-link
                v-for="(action, i) in actions"
                :key="i"
                :to="action.to(row)"
                class="text-sm font-medium text-gray-700 underline underline-offset-2 hover:text-gray-900"
              >
                {{ action.label }}
              </router-link>
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div
    v-show="!loading && data.length > 0"
    class="flex items-center justify-between mt-4 text-sm text-gray-600"
  >
    <span>Halaman {{ currentPage }} dari {{ totalPages }}</span>

    <div class="flex items-center gap-2">
      <button
        type="button"
        :disabled="currentPage <= 1"
        class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus:outline-none"
        aria-label="Previous page"
        @click="emit('change-page', currentPage - 1)"
      >
        &larr; Sebelumnya
      </button>

      <button
        type="button"
        :disabled="currentPage >= totalPages"
        class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus:outline-none"
        aria-label="Next page"
        @click="emit('change-page', currentPage + 1)"
      >
        Berikutnya &rarr;
      </button>
    </div>
  </div>
</template>
