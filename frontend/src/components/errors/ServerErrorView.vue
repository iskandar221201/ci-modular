<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-center p-6">
    <h1 class="text-6xl font-bold text-gray-900">500</h1>
    <p class="mt-4 text-lg text-gray-600">Terjadi kesalahan pada server.</p>
    <button
      type="button"
      class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700"
      @click="router.go(0)"
    >
      Muat Ulang
    </button>
  </div>
</template>
