<script setup>
import { ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import Badge from '@/components/ui/Badge.vue'
import SearchBar from '@/components/ui/SearchBar.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import LoadingOverlay from '@/components/ui/LoadingOverlay.vue'
import FlashMessage from '@/components/ui/FlashMessage.vue'
import DetailCard from '@/components/ui/DetailCard.vue'
import Datepicker from '@/components/ui/Datepicker.vue'
import CurrencyInput from '@/components/ui/CurrencyInput.vue'
import FormSubmitGroup from '@/components/ui/FormSubmitGroup.vue'
import { useConfirmDialog } from '@/composables/useConfirmDialog'

const breadcrumbs = [{ label: 'Component Gallery' }]

const badges = [
  { color: 'green', label: 'Active' },
  { color: 'red', label: 'Inactive' },
  { color: 'yellow', label: 'Pending' },
  { color: 'blue', label: 'Verified' },
  { color: 'gray', label: 'Draft' },
]

const mockUsers = [
  { name: 'John Doe', email: 'john@example.com', role: 'Admin' },
  { name: 'Jane Smith', email: 'jane@example.com', role: 'User' },
  { name: 'Bob Johnson', email: 'bob@example.com', role: 'Editor' },
  { name: 'Alice Williams', email: 'alice@example.com', role: 'User' },
  { name: 'Charlie Brown', email: 'charlie@example.com', role: 'Admin' },
]

const detailFields = [
  { label: 'Nama', value: 'John Doe' },
  { label: 'Email', value: 'john@example.com' },
  { label: 'Telepon', value: '+62 812 3456 7890' },
  { label: 'Role', value: 'Administrator' },
  { label: 'Tanggal Lahir', value: '17 Agustus 1990' },
  { label: 'Catatan', value: null },
]

const searchDemo = ref('')
const loadingVisible = ref(false)
const selectedDate = ref(new Date().toISOString().slice(0, 10))
const price = ref('1500000')

const {
  visible: confirmVisible,
  message: confirmMessage,
  open: openConfirm,
  confirm: doConfirm,
  cancel: cancelConfirm,
} = useConfirmDialog()

function demoConfirm() {
  openConfirm('Apakah Anda yakin ingin menghapus data contoh ini?', () => {})
}

const codeBlocks = {
  badge: `<Badge label="Active" color="green" />`,
  search: `<SearchBar v-model="search" @input="onSearch" />`,
  confirm: `<ConfirmDialog :visible="visible" :message="message" @confirm="..." @cancel="..." />`,
  empty: `<EmptyState message="Belum ada data pengguna." :cta="{ label: 'Tambah Pengguna', url: '/users/create' }" />`,
  loading: `<LoadingOverlay :visible="visible" />`,
  detail: `<DetailCard title="Informasi Pengguna" :fields="[...]" />`,
  datatable: `<DataTable :columns="columns" :data="data" :loading="loading" />`,
  datepicker: `<Datepicker name="release_date" label="Tanggal Rilis" v-model="date" />`,
  currency: `<CurrencyInput name="price" label="Harga" v-model="price" />`,
  submit: `<FormSubmitGroup :is-submitting="false" cancel-url="/users" />`,
}
</script>

<template>
  <PageHeader title="Component Gallery" :breadcrumbs="breadcrumbs" />

  <div class="space-y-6">
    <!-- Intro -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
      <p class="text-sm text-gray-600">
        Halaman ini menampilkan seluruh komponen UI yang tersedia di
        <span class="font-semibold text-gray-900">CI4 Kit</span> (versi Vue). Setiap bagian
        menyertakan pratinjau langsung dan cuplikan kode.
      </p>
    </div>

    <!-- Flash / Alert -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">flash / alert</h2>
      <p class="text-sm text-gray-500 mb-4">Menampilkan pesan flash. Sukses hijau — error merah.</p>
      <div class="bg-white rounded-lg border border-gray-200 p-6 space-y-3">
        <FlashMessage type="success" message="Data berhasil disimpan." />
        <FlashMessage type="error" message="Gagal menyimpan data. Silakan coba lagi." />
      </div>
    </div>

    <!-- Badge -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">badge</h2>
      <p class="text-sm text-gray-500 mb-4">Status pill dengan kode warna.</p>
      <div
        class="bg-white rounded-lg border border-gray-200 p-6 flex flex-wrap items-start gap-x-10 gap-y-4"
      >
        <div v-for="b in badges" :key="b.color" class="flex flex-col items-center gap-1.5">
          <Badge :label="b.label" :color="b.color" />
          <span class="text-xs text-gray-400 font-mono">{{ b.color }}</span>
        </div>
      </div>
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.badge }}</code></pre>
    </div>

    <!-- Search Bar -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">search_bar</h2>
      <p class="text-sm text-gray-500 mb-4">Input pencarian dengan ikon kaca pembesar.</p>
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <SearchBar v-model="searchDemo" placeholder="Cari sesuatu..." />
      </div>
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.search }}</code></pre>
    </div>

    <!-- Confirm Dialog -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">confirm_dialog</h2>
      <p class="text-sm text-gray-500 mb-4">Modal konfirmasi untuk aksi destruktif.</p>
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <button
          type="button"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700 focus:outline-none transition-colors"
          @click="demoConfirm()"
        >
          Hapus Data Contoh
        </button>
      </div>
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.confirm }}</code></pre>
    </div>

    <!-- Empty State -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">empty_state</h2>
      <p class="text-sm text-gray-500 mb-4">Tampilan ketika data kosong.</p>
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <EmptyState
          message="Belum ada data pengguna."
          :cta="{ label: 'Tambah Pengguna', url: '/users/create' }"
        />
      </div>
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.empty }}</code></pre>
    </div>

    <!-- Loading Overlay -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">loading_overlay</h2>
      <p class="text-sm text-gray-500 mb-4">Overlay halaman penuh dengan spinner.</p>
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <button
          type="button"
          class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 focus:outline-none transition-colors"
          @click="loadingVisible = true"
        >
          Tampilkan Overlay
        </button>
        <span class="ml-3 text-xs text-gray-400">Overlay mencakup seluruh viewport.</span>
      </div>
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.loading }}</code></pre>
    </div>

    <!-- Detail Card -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">detail_card</h2>
      <p class="text-sm text-gray-500 mb-4">Kartu informasi dengan pasangan label-nilai.</p>
      <DetailCard title="Informasi Pengguna" :fields="detailFields" />
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.detail }}</code></pre>
    </div>

    <!-- Datatable (mock) -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">datatable</h2>
      <p class="text-sm text-gray-500 mb-4">
        Tabel data dengan loading skeleton, empty state, dan paginasi.
      </p>
      <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap"
              >
                Nama
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap"
              >
                Email
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap"
              >
                Role
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="u in mockUsers" :key="u.email" class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ u.name }}</td>
              <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ u.email }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <Badge
                  :label="u.role"
                  :color="u.role === 'Admin' ? 'green' : u.role === 'Editor' ? 'blue' : 'gray'"
                />
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <a href="#" class="text-sm font-medium text-gray-700 underline underline-offset-2"
                  >Detail</a
                >
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.datatable }}</code></pre>
    </div>

    <!-- Datepicker -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">datepicker</h2>
      <p class="text-sm text-gray-500 mb-4">Pilih tanggal dengan kalender overlay.</p>
      <div class="bg-white rounded-lg border border-gray-200 p-6 max-w-sm">
        <Datepicker
          v-model="selectedDate"
          name="release_date"
          label="Tanggal Rilis"
          placeholder="Pilih tanggal"
        />
      </div>
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.datepicker }}</code></pre>
    </div>

    <!-- Currency Input -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">currency_input</h2>
      <p class="text-sm text-gray-500 mb-4">Input harga dengan format ribuan otomatis.</p>
      <div class="bg-white rounded-lg border border-gray-200 p-6 max-w-sm">
        <CurrencyInput v-model="price" name="price" label="Harga" placeholder="0" />
      </div>
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.currency }}</code></pre>
    </div>

    <!-- Submit Group -->
    <hr class="border-gray-200" />
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-1">submit_group</h2>
      <p class="text-sm text-gray-500 mb-4">Tombol submit + cancel untuk form actions.</p>
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <FormSubmitGroup :is-submitting="false" cancel-url="/users" />
      </div>
      <pre
        class="mt-3 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm text-gray-300 font-mono"
      ><code>{{ codeBlocks.submit }}</code></pre>
    </div>

    <div class="h-8"></div>
  </div>

  <ConfirmDialog
    :visible="confirmVisible"
    :message="confirmMessage"
    @confirm="doConfirm()"
    @cancel="cancelConfirm()"
  />
  <LoadingOverlay :visible="loadingVisible" />
</template>
