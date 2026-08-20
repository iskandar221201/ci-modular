<script setup>
import { onMounted } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import SearchBar from '@/components/ui/SearchBar.vue'
import DataTable from '@/components/ui/DataTable.vue'
import { useDataTable } from '@/composables/useDataTable'

const breadcrumbs = [{ label: 'Dashboard', url: '/dashboard' }, { label: 'Users' }]
const action = { label: 'Tambah User', url: '/users/create' }
const columns = [
  { key: 'username', label: 'Nama' },
  { key: 'email', label: 'Email' },
]
const actions = [{ label: 'Detail', to: (row) => `/users/${row.id}` }]

const { data, loading, currentPage, totalPages, search, onSearch, changePage, init } =
  useDataTable('/users')

onMounted(() => init())
</script>

<template>
  <PageHeader title="Daftar Users" :breadcrumbs="breadcrumbs" :action="action" />

  <div class="mb-4">
    <SearchBar v-model="search" placeholder="Cari nama atau email..." @input="onSearch" />
  </div>

  <DataTable
    :columns="columns"
    :actions="actions"
    :data="data"
    :loading="loading"
    :current-page="currentPage"
    :total-pages="totalPages"
    @change-page="changePage"
  />
</template>
