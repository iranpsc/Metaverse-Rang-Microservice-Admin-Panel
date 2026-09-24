<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="لیست قیمت گذاری ها"
      subtitle="مشاهده درخواست‌های قیمت گذاری"
    />

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end mb-6">
      <div class="w-full sm:flex-1 sm:max-w-md">
        <SearchBox
          v-model="searchTerm"
          placeholder="جستجو..."
          :debounce-ms="500"
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>
      <div class="w-full sm:w-56">
        <Select
          v-model="sortBy"
          label="مرتب‌سازی بر اساس"
          placeholder=""
          :options="sortByOptions"
          @change="handleFilterChange"
        />
      </div>
      <div class="w-full sm:w-48">
        <Select
          v-model="sortDirection"
          label="ترتیب"
          placeholder=""
          :options="sortDirectionOptions"
          @change="handleFilterChange"
        />
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
      v-else-if="pricings && pricings.length > 0"
      :columns="tableColumns"
      :data="pricings"
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
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Alert, Select, PageHeader } from '../../components/ui'
import { gregorianToShamsiSync } from '../../utils/dateConverter'
import { formatDisplayTime } from '../../utils/dateFormatter'
import { formatLatinNumber } from '../../utils/numberFormatter'
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

const pricings = ref([])
const sortBy = ref('price_irr')
const sortDirection = ref('desc')

const sortByOptions = [
  { value: 'price_irr', label: 'مبلغ قیمت گذاری ریال' },
  { value: 'price_psc', label: 'مبلغ قیمت گذاری PSC' },
]

const sortDirectionOptions = [
  { value: 'desc', label: 'نزولی' },
  { value: 'asc', label: 'صعودی' },
]

// Table columns configuration
const tableColumns = [
  {
    key: 'property_id',
    label: 'کد زمین'
  },
  {
    key: 'price_psc',
    label: 'مبلغ قیمت گذاری psc'
  },
  {
    key: 'price_irr',
    label: 'مبلغ قیمت گذاری ریال'
  },
  {
    key: 'created_at_date',
    label: 'تاریخ قیمت گذاری'
  },
  {
    key: 'created_at_time',
    label: 'ساعت قیمت گذاری'
  }
]

const formatPrice = (value) => formatLatinNumber(value)

const handleFilterChange = () => {
  resetToFirstPage()
  fetchPricings()
}

const handleSearch = () => search(fetchPricings)
const handleClear = () => clear(fetchPricings)
const onPageChange = (page) => goToPage(page, fetchPricings)

const clearPricings = () => {
  pricings.value = []
  pagination.value = null
}

const fetchPricings = () => execute(async () => {
  const response = await apiClient.get('/lands/pricing', {
    params: buildParams({
      sort_by: sortBy.value,
      sort: sortDirection.value
    })
  })

  if (response.data.success) {
    pricings.value = response.data.data.pricings.map(pricing => ({
      property_id: pricing.feature?.properties?.id || '-',
      price_psc: formatPrice(pricing.price_psc),
      price_irr: formatPrice(pricing.price_irr),
      created_at_date: pricing.created_at
        ? (gregorianToShamsiSync(pricing.created_at) || '-')
        : '-',
      created_at_time: pricing.created_at
        ? formatDisplayTime(pricing.created_at, { useLocale: false, includeSeconds: true })
        : '-'
    }))
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات قیمت گذاری‌ها'
    clearPricings()
  }
}, {
  onClear: clearPricings,
  logLabel: 'Pricings fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

onMounted(() => {
  fetchPricings()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>
