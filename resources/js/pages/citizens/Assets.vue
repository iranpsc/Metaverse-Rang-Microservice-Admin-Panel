<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">دارایی های شهروندان</h1>
      <p class="text-[var(--theme-text-secondary)]">مشاهده و مدیریت دارایی‌های کاربران</p>
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
      :data="assets"
      :pagination="pagination"
      empty-state-message="اطلاعاتی تعریف نشده است"
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
import { Table, Pagination, LoadingState, ErrorState } from '../../components/ui'

const loading = ref(true)
const error = ref(null)
const assets = ref([])
const pagination = ref(null)
const currentPage = ref(1)

// Table columns configuration
const tableColumns = [
  {
    key: 'user_name',
    label: 'نام کاربر'
  },
  {
    key: 'psc',
    label: 'دارایی های PSC'
  },
  {
    key: 'blue',
    label: 'دارایی های رنگ آبی'
  },
  {
    key: 'red',
    label: 'دارایی های رنگ قرمز'
  },
  {
    key: 'yellow',
    label: 'دارایی های رنگ زرد'
  },
  {
    key: 'irr',
    label: 'دارایی های ریال'
  },
  {
    key: 'features_count',
    label: 'تعداد املاک'
  }
]

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchAssets()
  }
}

const fetchAssets = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10,
    }

    const response = await apiClient.get('/assets', { params })

    if (response.data.success) {
      assets.value = response.data.data.assets
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات دارایی‌ها'
    }
  } catch (err) {
    console.error('Assets fetch error:', err)

    // If 401/403, don't set error message - axios interceptor will handle redirect
    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      assets.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    assets.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAssets()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

