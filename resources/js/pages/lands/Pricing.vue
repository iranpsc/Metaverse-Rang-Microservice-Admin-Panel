<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">لیست قیمت گذاری ها</h1>
      <p class="text-[var(--theme-text-secondary)]">مشاهده درخواست‌های قیمت گذاری</p>
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
const pricings = ref([])
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

const handleSearch = () => {
  currentPage.value = 1
  fetchPricings()
}

const handleClear = () => {
  currentPage.value = 1
  fetchPricings()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchPricings()
  }
}

const fetchPricings = async () => {
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

    const response = await apiClient.get('/lands/pricing', { params })

    if (response.data.success) {
      pricings.value = response.data.data.pricings.map(pricing => ({
        property_id: pricing.feature?.properties?.id || '-',
        price_psc: pricing.price_psc || 0,
        price_irr: pricing.price_irr || 0,
        created_at_date: pricing.created_at ? formatDate(pricing.created_at) : '-',
        created_at_time: pricing.created_at ? formatTime(pricing.created_at) : '-'
      }))
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات قیمت گذاری‌ها'
    }
  } catch (err) {
    console.error('Pricings fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      pricings.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    pricings.value = []
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
  fetchPricings()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

