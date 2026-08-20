<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import UserForm from '@/modules/users/components/UserForm.vue'
import { useToastStore } from '@/shared/stores/toast'
import { useUsers } from '@/modules/users/composables/useUsers'

const breadcrumbs = [
  { label: 'Dashboard', url: '/dashboard' },
  { label: 'Users', url: '/users' },
  { label: 'Tambah' },
]

const router = useRouter()
const users = useUsers()
const toast = useToastStore()
const errors = ref({})
const isSubmitting = ref(false)

async function onSubmit(payload) {
  isSubmitting.value = true
  errors.value = {}
  try {
    await users.create(payload)
    toast.show('User berhasil dibuat', 'info')
    router.push('/users')
  } catch (err) {
    if (err && err.errors) errors.value = err.errors
    else toast.catch(err)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <PageHeader title="Tambah User" :breadcrumbs="breadcrumbs" />

  <div class="mt-6 bg-white rounded-lg border border-gray-200 p-6 max-w-md">
    <UserForm
      mode="create"
      :errors="errors"
      :is-submitting="isSubmitting"
      cancel-url="/users"
      @submit="onSubmit"
    />
  </div>
</template>