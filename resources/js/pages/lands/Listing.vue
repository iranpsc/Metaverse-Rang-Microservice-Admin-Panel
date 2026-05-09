<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">لیست املاک</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت و مشاهده اطلاعات املاک</p>
    </div>

    <!-- Search Box -->
    <div class="mb-6">
      <SearchBox
        v-model="searchTerm"
        placeholder="شناسه ملک را وارد کنید"
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
      v-else-if="properties && properties.length > 0"
      :columns="tableColumns"
      :data="properties"
      :pagination="pagination"
      show-row-number
    >
    </Table>

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
const properties = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)

// Table columns configuration
const tableColumns = [
  {
    key: 'id',
    label: 'کد زمین'
  },
  {
    key: 'area',
    label: 'مساحت'
  },
  {
    key: 'density',
    label: 'تراکم'
  },
  {
    key: 'application_title',
    label: 'نوع کاربری'
  },
  {
    key: 'address',
    label: 'آدرس'
  },
  {
    key: 'date',
    label: 'تاریخ ثبت'
  },
  {
    key: 'publisher_name',
    label: 'ثبت کننده'
  }
]

const handleSearch = () => {
  currentPage.value = 1
  fetchProperties()
}

const handleClear = () => {
  currentPage.value = 1
  fetchProperties()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchProperties()
  }
}

const fetchProperties = async () => {
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

    const response = await apiClient.get('/lands', { params })

    if (response.data.success) {
      // Map the response data to match table structure
      properties.value = response.data.data.properties.map(property => ({
        id: property.id,
        area: property.area,
        density: property.density,
        application_title: getApplicationTitle(property.karbari),
        address: property.address?.length > 15 ? property.address.substring(0, 15) + '...' : property.address,
        date: property.date ? formatDate(property.date) : '-',
        publisher_name: property.feature?.map?.publisher_name || '-',
        feature: property.feature
      }))
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات املاک'
    }
  } catch (err) {
    console.error('Properties fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      properties.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    properties.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

// Application title mapping based on karbari field
const getApplicationTitle = (karbari) => {
  if (!karbari) return '-'

  const mapping = {
    'm': 'مسکونی',
    't': 'تجاری',
    'a': 'آموزشی',
    's': 'فضای سبز',
    'f': 'فرهنگی',
    'p': 'پارکینگ',
    'z': 'مذهبی',
    'n': 'نمایشگاه',
    'g': 'گردشگری',
    'e': 'اداری',
    'b': 'بهداشتی'
  }

  return mapping[karbari] || '-'
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
  fetchProperties()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

