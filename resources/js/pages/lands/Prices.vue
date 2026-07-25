<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">قیمت زمین ها</h1>
      <p class="text-[var(--theme-text-secondary)]">مشاهده قیمت‌های زمین‌ها</p>
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
const features = ref([])
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
    key: 'application_title',
    label: 'کاربری'
  },
  {
    key: 'stability',
    label: 'قیمت اولیه'
  },
  {
    key: 'minimum_price_percentage',
    label: 'درصد پیشنهادی'
  },
  {
    key: 'updated_at',
    label: 'تاریخ ثبت پیشنهاد قیمت'
  }
]

const handleSearch = () => {
  currentPage.value = 1
  fetchFeatures()
}

const handleClear = () => {
  currentPage.value = 1
  fetchFeatures()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchFeatures()
  }
}

const fetchFeatures = async () => {
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

    const response = await apiClient.get('/lands/prices', { params })

    if (response.data.success) {
      features.value = response.data.data.features.map(feature => ({
        property_id: feature.properties?.id || '-',
        application_title: feature.properties?.getApplicationTitle || feature.properties?.application_title || '-',
        stability: feature.properties?.stability || 0,
        minimum_price_percentage: feature.properties?.minimum_price_percentage || '-',
        updated_at: feature.properties?.updated_at ? formatDate(feature.properties.updated_at) : '-'
      }))
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات قیمت‌ها'
    }
  } catch (err) {
    console.error('Features prices fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      features.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    features.value = []
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

onMounted(() => {
  fetchFeatures()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

