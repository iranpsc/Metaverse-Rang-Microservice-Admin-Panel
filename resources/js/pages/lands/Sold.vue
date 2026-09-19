<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="لیست زمین های فروخته شده"
      subtitle="مشاهده زمین‌های فروخته شده"
    />

    <!-- Search Box -->
    <div class="mb-6">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس نام خریدار، کد خریدار یا کد زمین..."
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
      @page-change="onPageChange"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { gregorianToShamsiSync } from '../../utils/dateConverter'
import { formatDisplayTime } from '../../utils/dateFormatter'
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Alert, PageHeader } from '../../components/ui'
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

const trades = ref([])

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
    key: 'buyer_code',
    label: 'شناسه شهروندی'
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

const clearTrades = () => {
  trades.value = []
  pagination.value = null
}

const fetchTrades = () => execute(async () => {
  const response = await apiClient.get('/lands/sold', { params: buildParams() })

  if (response.data.success) {
    trades.value = response.data.data.trades.map(trade => ({
      property_id: trade.feature?.properties?.id || '-',
      buyer_name: trade.buyer?.name || '-',
      buyer_code: trade.buyer?.code || '-',
      created_at_date: trade.created_at
        ? (gregorianToShamsiSync(trade.created_at) || '-')
        : '-',
      created_at_time: trade.created_at
        ? formatDisplayTime(trade.created_at, { useLocale: false, includeSeconds: true })
        : '-'
    }))
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات زمین‌های فروخته شده'
    clearTrades()
  }
}, {
  onClear: clearTrades,
  logLabel: 'Sold lands fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

const handleSearch = () => search(fetchTrades)
const handleClear = () => clear(fetchTrades)
const onPageChange = (page) => goToPage(page, fetchTrades)

onMounted(() => {
  fetchTrades()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

