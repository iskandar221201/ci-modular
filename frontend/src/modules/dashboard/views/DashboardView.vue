<script setup>
import PageHeader from '@/components/ui/PageHeader.vue'

const breadcrumbs = [{ label: 'Dashboard' }]
</script>

<template>
  <PageHeader title="Dashboard" :breadcrumbs="breadcrumbs" />

  <div class="mt-6 bg-white rounded-lg border border-gray-200 p-6">
    <h2 class="text-base font-semibold text-gray-900 mb-2">Selamat Datang di Dashboard!</h2>
    <p class="text-sm text-gray-500">
      Anda telah berhasil login. Ini adalah halaman dashboard utama.
    </p>
  </div>
</template>
