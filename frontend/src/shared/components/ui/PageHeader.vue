<script setup>
defineProps({
  title: { type: String, required: true },
  breadcrumbs: { type: Array, default: () => [] },
  action: { type: Object, default: null },
})
</script>

<template>
  <div class="mb-6">
    <div class="flex flex-col lg:flex-row items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>

        <nav v-if="breadcrumbs.length" aria-label="Breadcrumb" class="mt-1">
          <ol class="flex items-center gap-1.5 text-sm text-gray-500">
            <template v-for="(crumb, i) in breadcrumbs" :key="i">
              <li v-if="i > 0" aria-hidden="true">
                <svg
                  class="w-3.5 h-3.5 text-gray-400 flex-shrink-0"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8.25 4.5l7.5 7.5-7.5 7.5"
                  />
                </svg>
              </li>
              <li>
                <router-link
                  v-if="crumb.url"
                  :to="crumb.url"
                  class="hover:text-gray-900 hover:underline underline-offset-2 transition-colors"
                >
                  {{ crumb.label }}
                </router-link>
                <span v-else class="text-gray-700 font-medium" aria-current="page">{{
                  crumb.label
                }}</span>
              </li>
            </template>
          </ol>
        </nav>
      </div>

      <router-link
        v-if="action"
        :to="action.url"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 focus:outline-none transition-colors flex-shrink-0"
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
        {{ action.label }}
      </router-link>
    </div>
  </div>
</template>
