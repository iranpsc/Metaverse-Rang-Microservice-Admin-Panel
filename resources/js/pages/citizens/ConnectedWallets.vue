<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="کیف پول‌های متصل"
      subtitle="لیست شهروندانی که کیف پول رمزارزی خود را به حساب متصل کرده‌اند"
    />

    <div class="mb-6">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس نام، کد یا آدرس کیف پول..."
        :debounce-ms="500"
        container-class="max-w-md"
        @search="handleSearch"
        @clear="handleClear"
      />
    </div>

    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <Table
      v-else
      :columns="tableColumns"
      :data="users"
      :pagination="pagination"
      empty-state-message="کاربری با کیف پول متصل یافت نشد"
    >
      <template #cell-wallet_address="{ value }">
        <span
          class="font-mono text-[var(--theme-text-secondary)]"
          :title="value"
        >
          {{ truncateMiddle(value) }}
        </span>
      </template>
    </Table>

    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="onPageChange"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, SearchBox, LoadingState, ErrorState, PageHeader } from '../../components/ui'
import { truncateMiddle } from '../../utils/text'
import { usePaginatedList } from '../../composables/usePaginatedList'

const {
  loading,
  error,
  pagination,
  searchTerm,
  execute,
  search,
  clear,
  goToPage,
  buildParams
} = usePaginatedList()

const users = ref([])

const tableColumns = [
  {
    key: 'name',
    label: 'نام'
  },
  {
    key: 'code',
    label: 'کد',
    textSecondary: true,
    defaultValue: '-'
  },
  {
    key: 'wallet_address',
    label: 'آدرس کیف پول'
  },
  {
    key: 'registered_at',
    label: 'تاریخ اتصال',
    textSecondary: true,
    defaultValue: '-'
  }
]

const clearUsers = () => {
  users.value = []
  pagination.value = null
}

const fetchConnectedWallets = () => execute(async () => {
  const response = await apiClient.get('/connected-wallets', { params: buildParams() })

  if (response.data.success) {
    users.value = response.data.data.users
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت لیست کیف پول‌های متصل'
    clearUsers()
  }
}, {
  onClear: clearUsers,
  logLabel: 'Connected wallets fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

const handleSearch = () => search(fetchConnectedWallets)
const handleClear = () => clear(fetchConnectedWallets)
const onPageChange = (page) => goToPage(page, fetchConnectedWallets)

onMounted(() => {
  fetchConnectedWallets()
})
</script>
