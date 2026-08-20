<script setup>
import { reactive } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import FormSubmitGroup from '@/components/ui/FormSubmitGroup.vue'
import { useForm } from '@/composables/useForm'
import { useToastStore } from '@/stores/toast'

const breadcrumbs = [
  { label: 'Dashboard', url: '/dashboard' },
  { label: 'Users', url: '/users' },
  { label: 'Tambah' },
]

const user = reactive({ username: '', email: '', password: '' })
const { errors, isSubmitting, submit } = useForm('/users', 'POST', '/users')
const toast = useToastStore()

async function onSubmit() {
  const ok = await submit(user)
  if (ok) toast.show('User berhasil dibuat', 'info')
}
</script>

<template>
  <PageHeader title="Tambah User" :breadcrumbs="breadcrumbs" />

  <div class="mt-6 bg-white rounded-lg border border-gray-200 p-6 max-w-md">
    <form @submit.prevent="onSubmit()">
      <div class="space-y-5">
        <div>
          <label for="username" class="block mb-1.5 text-sm font-medium text-gray-700"
            >Username</label
          >
          <input
            id="username"
            v-model="user.username"
            type="text"
            class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition"
            :class="
              errors.username
                ? 'border-red-400 focus:ring-red-400'
                : 'border-gray-300 focus:ring-gray-400'
            "
            required
          />
          <span v-show="errors.username" class="mt-1 text-xs text-red-600 block">{{
            errors.username
          }}</span>
        </div>

        <div>
          <label for="email" class="block mb-1.5 text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            v-model="user.email"
            type="email"
            class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition"
            :class="
              errors.email
                ? 'border-red-400 focus:ring-red-400'
                : 'border-gray-300 focus:ring-gray-400'
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
            v-model="user.password"
            type="text"
            placeholder="Minimal 8 karakter"
            class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition font-mono"
            :class="
              errors.password
                ? 'border-red-400 focus:ring-red-400'
                : 'border-gray-300 focus:ring-gray-400'
            "
            required
          />
          <p class="mt-1 text-xs text-gray-400">
            Password akan diberikan ke user untuk login pertama kali.
          </p>
          <span v-show="errors.password" class="mt-1 text-xs text-red-600 block">{{
            errors.password
          }}</span>
        </div>

        <FormSubmitGroup :is-submitting="isSubmitting" cancel-url="/users" />
      </div>
    </form>
  </div>
</template>
