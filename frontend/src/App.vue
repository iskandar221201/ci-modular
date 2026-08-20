<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useHead } from '@unhead/vue'
import AppShell from '@/shared/components/layout/AppShell.vue'
import AuthLayout from '@/shared/components/layout/AuthLayout.vue'
import ErrorToast from '@/shared/components/ui/ErrorToast.vue'
import { pageTitle } from '@/shared/utils/meta'

const route = useRoute()
const layout = computed(() => route.meta.layout || 'default')

useHead({ title: computed(() => pageTitle(route.meta.title || '')) })
</script>

<template>
  <AppShell v-if="layout === 'default'">
    <router-view />
  </AppShell>
  <AuthLayout v-else-if="layout === 'auth'">
    <router-view />
  </AuthLayout>
  <template v-else>
    <router-view />
  </template>
  <ErrorToast />
</template>
