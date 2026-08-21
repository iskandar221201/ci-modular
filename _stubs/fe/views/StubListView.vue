<script setup>
import { onMounted } from 'vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import SearchBar from '@/shared/components/ui/SearchBar.vue'
import DataTable from '@/shared/components/ui/DataTable.vue'
import { useDataTable } from '@/shared/composables/useDataTable'

const breadcrumbs = [{ label: 'Dashboard', url: '/dashboard' }, { label: '{{MODULES}}' }]
const action = { label: 'Tambah {{MODULE}}', url: '/{{MODULES_LOWER}}/create' }
const columns = [
  { key: 'id', label: 'ID' },
  { key: 'name', label: 'Nama' },
]
const actions = [{ label: 'Detail', to: (row) => `/{{MODULES_LOWER}}/${row.id}` }]

const { data, loading, currentPage, totalPages, search, onSearch, changePage, init } =
  useDataTable('/{{MODULES_LOWER}}')

onMounted(() => init())
</script>

<template>
  <PageHeader title="Daftar {{MODULES}}" :breadcrumbs="breadcrumbs" :action="action" />

  <div class="mb-4">
    <SearchBar v-model="search" placeholder="Cari {{MODULES_LOWER}}..." @input="onSearch" />
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
