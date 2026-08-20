<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useToastStore } from '@/stores/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import Skeleton from '@/components/ui/Skeleton.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import FormSubmitGroup from '@/components/ui/FormSubmitGroup.vue'
import { useDetailFetcher } from '@/composables/useDetailFetcher'
import { useConfirmDialog } from '@/composables/useConfirmDialog'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()

const id = route.params.id
const breadcrumbs = [
  { label: 'Dashboard', url: '/dashboard' },
  { label: 'Users', url: '/users' },
  { label: 'Detail' },
]

const { data, loading, init } = useDetailFetcher(`/users/${id}`)
const editMode = ref(false)
const user = reactive({ username: '', email: '' })
const errors = ref({})
const isSubmitting = ref(false)

const {
  visible: confirmVisible,
  message: confirmMessage,
  open: openConfirm,
  confirm: doConfirm,
  cancel: cancelConfirm,
} = useConfirmDialog()

onMounted(async () => {
  await init()
  user.username = data.value.username
  user.email = data.value.email
})

async function submitForm() {
  isSubmitting.value = true
  errors.value = {}
  try {
    await api.put(`/users/${id}`, { username: user.username, email: user.email })
    data.value.username = user.username
    data.value.email = user.email
    editMode.value = false
    toast.show('User berhasil diupdate', 'info')
  } catch (err) {
    if (err && err.errors) errors.value = err.errors
  } finally {
    isSubmitting.value = false
  }
}

function cancelEdit() {
  editMode.value = false
  user.username = data.value.username
  user.email = data.value.email
}

function openDelete() {
  openConfirm('Apakah Anda yakin ingin menghapus user ini?', async () => {
    try {
      await api.delete(`/users/${id}`)
      router.push('/users')
    } catch (err) {
      toast.catch(err)
    }
  })
}
</script>

<template>
  <PageHeader title="Detail User" :breadcrumbs="breadcrumbs" />

  <div v-show="loading" class="mt-6 bg-white rounded-lg border border-gray-200 p-6">
    <div class="flex items-start justify-between mb-4">
      <Skeleton height="1rem" width="8rem" />
      <Skeleton height="0.875rem" width="3rem" />
    </div>
    <div class="space-y-5">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <Skeleton height="0.875rem" width="5rem" />
        <Skeleton height="0.875rem" width="70%" />
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <Skeleton height="0.875rem" width="5rem" />
        <Skeleton height="0.875rem" width="85%" />
      </div>
    </div>
  </div>

  <div v-show="!loading" class="mt-6 space-y-4">
    <!-- View Mode -->
    <div v-show="!editMode" class="bg-white rounded-lg border border-gray-200 p-6">
      <div class="flex items-start justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900">Informasi User</h3>
        <button
          type="button"
          class="text-sm font-medium text-gray-500 hover:text-gray-900 underline underline-offset-2 flex-shrink-0 ml-4"
          @click="editMode = true"
        >
          Edit
        </button>
      </div>

      <dl class="space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <dt class="text-sm text-gray-500">Username</dt>
          <dd class="text-sm text-gray-900 col-span-1 sm:col-span-2">{{ data.username || '—' }}</dd>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <dt class="text-sm text-gray-500">Email</dt>
          <dd class="text-sm text-gray-900 col-span-1 sm:col-span-2">{{ data.email || '—' }}</dd>
        </div>
      </dl>

      <div class="mt-6 pt-4 border-t border-gray-100">
        <button
          type="button"
          class="text-sm text-red-600 hover:text-red-800 font-medium focus:outline-none"
          @click="openDelete()"
        >
          Hapus User
        </button>
      </div>
    </div>

    <!-- Edit Mode -->
    <div v-show="editMode" class="bg-white rounded-lg border border-gray-200 p-6">
      <h3 class="text-sm font-semibold text-gray-900 mb-4">Edit User</h3>
      <form @submit.prevent="submitForm()">
        <div class="space-y-5 max-w-md">
          <div>
            <label for="edit_username" class="block mb-1.5 text-sm font-medium text-gray-700">
              Username
            </label>
            <input
              id="edit_username"
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
            <label for="edit_email" class="block mb-1.5 text-sm font-medium text-gray-700"
              >Email</label
            >
            <input
              id="edit_email"
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

          <FormSubmitGroup :is-submitting="isSubmitting" @cancel="cancelEdit()" />
        </div>
      </form>
    </div>
  </div>

  <ConfirmDialog
    :visible="confirmVisible"
    :message="confirmMessage"
    @confirm="doConfirm()"
    @cancel="cancelConfirm()"
  />
</template>
