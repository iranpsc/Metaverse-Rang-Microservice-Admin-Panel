<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="قیمت زمین ها"
      subtitle="مشاهده قیمت‌های زمین‌ها"
    />

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
      v-else-if="features && features.length > 0"
      :columns="tableColumns"
      :data="features"
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
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Alert, PageHeader } from '../../components/ui'
import { gregorianToShamsiSync } from '../../utils/dateConverter'
import { getKarbariLabel } from '../../utils/lands/karbariLabels'
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

const features = ref([])

// Table columns configuration
const tableColumns = [
  {
    key: 'property_id',
    label: 'کد زمین'
  },
  {
    key: 'application_title',
    label: 'کاربری'
  },
  {
    key: 'stability',
    label: 'قیمت اولیه'
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
    key: 'minimum_price_percentage',
    label: 'کف قیمت ثبت شده'
  },
  {
    key: 'updated_at',
    label: 'تاریخ ثبت قیمت'
  }
]

const clearFeatures = () => {
  features.value = []
  pagination.value = null
}

const fetchFeatures = () => execute(async () => {
  const response = await apiClient.get('/lands/prices', { params: buildParams() })

  if (response.data.success) {
    features.value = response.data.data.features.map(feature => ({
      property_id: feature.properties?.id || '-',
      application_title: getKarbariLabel(feature.properties?.karbari),
      stability: feature.properties?.stability || 0,
      price_psc: feature.properties?.price_psc ?? 0,
      price_irr: feature.properties?.price_irr ?? 0,
      minimum_price_percentage: feature.properties?.minimum_price_percentage || '-',
      updated_at: feature.properties?.updated_at
        ? (gregorianToShamsiSync(feature.properties.updated_at) || '-')
        : '-'
    }))
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات قیمت‌ها'
    clearFeatures()
  }
}, {
  onClear: clearFeatures,
  logLabel: 'Features prices fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

const handleSearch = () => search(fetchFeatures)
const handleClear = () => clear(fetchFeatures)
const onPageChange = (page) => goToPage(page, fetchFeatures)

onMounted(() => {
  fetchFeatures()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>