<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToastStore } from '@/shared/stores/toast'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import Skeleton from '@/shared/components/ui/Skeleton.vue'
import ConfirmDialog from '@/shared/components/ui/ConfirmDialog.vue'
import UserForm from '@/modules/users/components/UserForm.vue'
import { useUsers } from '@/modules/users/composables/useUsers'
import { useConfirmDialog } from '@/shared/composables/useConfirmDialog'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()
const users = useUsers()

const id = route.params.id
const breadcrumbs = [
  { label: 'Dashboard', url: '/dashboard' },
  { label: 'Users', url: '/users' },
  { label: 'Detail' },
]

const editMode = ref(false)
const errors = ref({})
const isSubmitting = ref(false)

const {
  visible: confirmVisible,
  message: confirmMessage,
  open: openConfirm,
  confirm: doConfirm,
  cancel: cancelConfirm,
} = useConfirmDialog()

onMounted(() => users.fetchOne(id))

function startEdit() {
  errors.value = {}
  editMode.value = true
}

async function onSubmit(payload) {
  isSubmitting.value = true
  errors.value = {}
  try {
    await users.update(id, payload)
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
  errors.value = {}
}

function openDelete() {
  openConfirm('Apakah Anda yakin ingin menghapus user ini?', async () => {
    try {
      await users.remove(id)
      router.push('/users')
    } catch (err) {
      toast.catch(err)
    }
  })
}

const data = computed(() => users.current ?? {})
</script>

<template>
  <PageHeader title="Detail User" :breadcrumbs="breadcrumbs" />

  <div v-show="users.loading" class="mt-6 bg-white rounded-lg border border-gray-200 p-6">
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

  <div v-show="!users.loading" class="mt-6 space-y-4">
    <!-- View Mode -->
    <div v-show="!editMode" class="bg-white rounded-lg border border-gray-200 p-6">
      <div class="flex items-start justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900">Informasi User</h3>
        <button
          type="button"
          class="text-sm font-medium text-gray-500 hover:text-gray-900 underline underline-offset-2 flex-shrink-0 ml-4"
          @click="startEdit()"
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
      <div class="max-w-md">
        <UserForm
          mode="edit"
          :initial="data"
          :errors="errors"
          :is-submitting="isSubmitting"
          @submit="onSubmit"
          @cancel="cancelEdit"
        />
      </div>
    </div>
  </div>

  <ConfirmDialog
    :visible="confirmVisible"
    :message="confirmMessage"
    @confirm="doConfirm()"
    @cancel="cancelConfirm()"
  />
</template>