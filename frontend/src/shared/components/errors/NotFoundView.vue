<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-center p-6">
    <h1 class="text-6xl font-bold text-gray-900">404</h1>
    <p class="mt-4 text-lg text-gray-600">Halaman tidak ditemukan.</p>
    <router-link
      to="/dashboard"
      class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700"
    >
      Kembali ke Dashboard
    </router-link>
  </div>
</template>
