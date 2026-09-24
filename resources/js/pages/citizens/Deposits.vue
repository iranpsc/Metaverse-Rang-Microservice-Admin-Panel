<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="واریزی ها"
      subtitle="مشاهده و مدیریت تراکنش‌های واریزی کاربران"
    />

    <!-- Search and Actions Row -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between mb-6">
      <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center flex-1 w-full">
        <div class="flex-1 max-w-md">
          <SearchBox
            v-model="searchTerm"
            placeholder="جستجو بر اساس نام کاربر، کد شهروندی یا شماره مرجع"
            :debounce-ms="500"
            @search="handleSearch"
            @clear="handleClear"
          />
        </div>
        <div class="w-full sm:w-48">
          <Select
            v-model="productFilter"
            :options="productOptions"
            @change="handleProductFilterChange"
          />
        </div>
      </div>
      <div class="flex gap-2">
        <Button
          variant="primary"
          @click="handleExport"
          :loading="exporting"
        >
          دانلود خروجی اکسل
        </Button>
      </div>
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
      v-else
      :columns="tableColumns"
      :data="payments"
      :pagination="pagination"
      empty-state-message="تراکنشی یافت نشد"
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
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Button, Select, PageHeader } from '../../components/ui'
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
  buildParams,
  resetToFirstPage
} = usePaginatedList()

const payments = ref([])
const productFilter = ref('all')
const exporting = ref(false)

const productOptions = [
  { value: 'all', label: 'همه دارایی‌ها' },
  { value: 'irr', label: 'ریال' },
  { value: 'psc', label: 'PSC' },
  { value: 'red', label: 'رنگ قرمز' },
  { value: 'blue', label: 'رنگ آبی' },
  { value: 'yellow', label: 'رنگ زرد' },
]

// Table columns configuration
const tableColumns = [
  {
    key: 'user_name',
    label: 'نام کاربر'
  },
  {
    key: 'citizen_code',
    label: 'کد شهروندی',
    defaultValue: '-'
  },
  {
    key: 'amount',
    label: 'مبلغ تراکنش'
  },
  {
    key: 'ref_id',
    label: 'شماره مرجع بانک'
  },
  {
    key: 'card_pan',
    label: 'شماره کارت یا حساب مبدا'
  },
  {
    key: 'gateway',
    label: 'نام درگاه'
  },
  {
    key: 'product_title',
    label: 'محصول خریداری شده'
  },
  {
    key: 'created_at',
    label: 'تاریخ واریز'
  },
  {
    key: 'created_at_time',
    label: 'ساعت واریز'
  }
]

const handleSearch = () => search(fetchDeposits)
const handleClear = () => clear(fetchDeposits)
const handleProductFilterChange = () => {
  resetToFirstPage()
  fetchDeposits()
}
const onPageChange = (page) => goToPage(page, fetchDeposits)

const handleExport = async () => {
  try {
    exporting.value = true
    const response = await apiClient.get('/deposits/export', {
      responseType: 'blob'
    })

    // Create blob link to download
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'transactions.xlsx')
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Export error:', err)
    error.value = 'خطا در دانلود فایل اکسل'
  } finally {
    exporting.value = false
  }
}

const clearPayments = () => {
  payments.value = []
  pagination.value = null
}

const fetchDeposits = () => execute(async () => {
  const params = buildParams()
  if (productFilter.value && productFilter.value !== 'all') {
    params.product = productFilter.value
  }

  const response = await apiClient.get('/deposits', { params })

  if (response.data.success) {
    payments.value = response.data.data.payments
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات واریزی‌ها'
    clearPayments()
  }
}, {
  onClear: clearPayments,
  logLabel: 'Deposits fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

onMounted(() => {
  fetchDeposits()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>
