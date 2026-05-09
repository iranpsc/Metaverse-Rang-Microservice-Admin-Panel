<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">لیست زمین های فروخته شده</h1>
      <p class="text-[var(--theme-text-secondary)]">مشاهده زمین‌های فروخته شده</p>
    </div>

    <!-- Search Box -->
    <div class="mb-6">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو..."
        :debounce-ms="500"
        container-class="max-w-md"
        @search="handleSearch"
        @clear="handleClear"
      />
    </div>

    <!-- Loading State -->
    <LoadingState v-if="loading" />

    <!-- Error State -->
    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <!-- Table -->
    <Table
      v-else-if="trades && trades.length > 0"
      :columns="tableColumns"
      :data="trades"
      :pagination="pagination"
      show-row-number
    />

    <!-- Empty State -->
    <Alert
      v-else
      variant="danger"
      message="ملکی یافت نشد"
      :dismissible="false"
    />

    <!-- Pagination -->
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
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Alert } from '../../components/ui'

const loading = ref(true)
const error = ref(null)
const trades = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)

// Table columns configuration
const tableColumns = [
  {
    key: 'property_id',
    label: 'کد زمین'
  },
  {
    key: 'buyer_name',
    label: 'خریدار'
  },
  {
    key: 'created_at_date',
    label: 'تاریخ خرید'
  },
  {
    key: 'created_at_time',
    label: 'ساعت خرید'
  }
]

const handleSearch = () => {
  currentPage.value = 1
  fetchTrades()
}

const handleClear = () => {
  currentPage.value = 1
  fetchTrades()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchTrades()
  }
}

const fetchTrades = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10
    }

    if (searchTerm.value) {
      params.search = searchTerm.value
    }

    const response = await apiClient.get('/lands/sold', { params })

    if (response.data.success) {
      trades.value = response.data.data.trades.map(trade => ({
        property_id: trade.feature?.properties?.id || '-',
        buyer_name: trade.buyer?.name || '-',
        created_at_date: trade.created_at ? formatDate(trade.created_at) : '-',
        created_at_time: trade.created_at ? formatTime(trade.created_at) : '-'
      }))
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات زمین‌های فروخته شده'
    }
  } catch (err) {
    console.error('Sold lands fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      trades.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    trades.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}/${month}/${day}`
}

const formatTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  const seconds = String(date.getSeconds()).padStart(2, '0')
  return `${hours}:${minutes}:${seconds}`
}

onMounted(() => {
  fetchTrades()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

