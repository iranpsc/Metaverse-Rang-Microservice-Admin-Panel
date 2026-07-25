<template>
  <div class="p-6 space-y-6">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">کیف پول‌های متصل</h1>
      <p class="text-[var(--theme-text-secondary)]">لیست شهروندانی که کیف پول رمزارزی خود را به حساب متصل کرده‌اند</p>
    </div>

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
          {{ truncateWalletAddress(value) }}
        </span>
      </template>
    </Table>

    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="goToPage"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, SearchBox, LoadingState, ErrorState } from '../../components/ui'

const loading = ref(true)
const error = ref(null)
const users = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)

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
    label: 'تاریخ ثبت نام',
    textSecondary: true,
    defaultValue: '-'
  }
]

const truncateWalletAddress = (address, start = 6, end = 4) => {
  if (!address || address.length <= start + end + 3) {
    return address || '-'
  }

  return `${address.slice(0, start)}...${address.slice(-end)}`
}

const handleSearch = () => {
  currentPage.value = 1
  fetchConnectedWallets()
}

const handleClear = () => {
  currentPage.value = 1
  fetchConnectedWallets()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchConnectedWallets()
  }
}

const fetchConnectedWallets = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10
    }

    if (searchTerm.value) {
      params.search = searchTerm.value.trim()
    }

    const response = await apiClient.get('/connected-wallets', { params })

    if (response.data.success) {
      users.value = response.data.data.users
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت لیست کیف پول‌های متصل'
    }
  } catch (err) {
    console.error('Connected wallets fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      users.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    users.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchConnectedWallets()
})
</script>
