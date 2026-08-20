<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const appName = import.meta.env.VITE_APP_NAME || 'CI4 Kit'
const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const errors = ref({})
const generalError = ref('')
const isSubmitting = ref(false)

async function doLogin() {
  isSubmitting.value = true
  errors.value = {}
  generalError.value = ''
  try {
    await auth.login({ email: email.value, password: password.value })
    router.push('/dashboard')
  } catch (err) {
    if (err?.errors) errors.value = err.errors
    else generalError.value = err?.message || 'Email atau password salah.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-white">
    <div class="w-full max-w-sm px-8 py-10">
      <div class="mb-8 text-center">
        <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">{{ appName }}</h1>
        <p class="mt-1 text-sm text-gray-500">Masuk ke akun Anda</p>
      </div>

      <form class="space-y-5" @submit.prevent="doLogin()">
        <div
          v-show="generalError"
          class="px-4 py-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg"
        >
          {{ generalError }}
        </div>

        <div>
          <label for="email" class="block mb-1.5 text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="nama@email.com"
            class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition placeholder-gray-400"
            :class="
              errors.email
                ? 'border-red-400 focus:ring-red-400 focus:border-red-400'
                : 'border-gray-300 focus:ring-gray-400 focus:border-gray-400'
            "
            required
          />
          <span v-show="errors.email" class="mt-1 text-xs text-red-600 block">{{
            errors.email
          }}</span>
        </div>

        <div>
          <label for="password" class="block mb-1.5 text-sm font-medium text-gray-700"
            >Password</label
          >
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="••••••••"
            class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition placeholder-gray-400"
            :class="
              errors.password
                ? 'border-red-400 focus:ring-red-400 focus:border-red-400'
                : 'border-gray-300 focus:ring-gray-400 focus:border-gray-400'
            "
            required
          />
          <span v-show="errors.password" class="mt-1 text-xs text-red-600 block">{{
            errors.password
          }}</span>
        </div>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="w-full py-2.5 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-show="isSubmitting">Memproses...</span>
          <span v-show="!isSubmitting">Masuk</span>
        </button>
      </form>
    </div>
  </div>
</template>
