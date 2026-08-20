<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useHead } from '@unhead/vue'
import AppShell from '@/components/layout/AppShell.vue'
import AuthLayout from '@/components/layout/AuthLayout.vue'
import ErrorToast from '@/components/ui/ErrorToast.vue'
import { pageTitle } from '@/utils/meta'

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
