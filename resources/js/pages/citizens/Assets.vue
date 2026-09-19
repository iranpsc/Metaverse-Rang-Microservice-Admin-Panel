<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="دارایی های شهروندان"
      subtitle="مشاهده و مدیریت دارایی‌های کاربران"
    />

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end mb-6">
      <div class="w-full sm:flex-1 sm:max-w-md">
        <SearchBox
          v-model="searchTerm"
          placeholder="جستجو بر اساس کد شهروندی..."
          :debounce-ms="500"
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>
      <div class="w-full sm:w-56">
        <Select
          v-model="assetFilter"
          label="نوع دارایی"
          placeholder=""
          :options="assetOptions"
          @change="handleFilterChange"
        />
      </div>
      <div class="w-full sm:w-48">
        <Select
          v-model="sortDirection"
          label="ترتیب"
          placeholder=""
          :options="sortOptions"
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
      @page-change="onPageChange"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Select, PageHeader } from '../../components/ui'
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

const assets = ref([])
const assetFilter = ref('psc')
const sortDirection = ref('desc')

const assetOptions = [
  { value: 'psc', label: 'دارایی های PSC' },
  { value: 'blue', label: 'دارایی های رنگ آبی' },
  { value: 'red', label: 'دارایی های رنگ قرمز' },
  { value: 'yellow', label: 'دارایی های رنگ زرد' },
  { value: 'irr', label: 'دارایی های ریال' },
  { value: 'features_count', label: 'تعداد املاک' },
]

const sortOptions = [
  { value: 'desc', label: 'نزولی' },
  { value: 'asc', label: 'صعودی' },
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

const handleFilterChange = () => {
  resetToFirstPage()
  fetchAssets()
}

const handleSearch = () => search(fetchAssets)
const handleClear = () => clear(fetchAssets)
const onPageChange = (page) => goToPage(page, fetchAssets)

const clearAssets = () => {
  assets.value = []
  pagination.value = null
}

const fetchAssets = () => execute(async () => {
  const response = await apiClient.get('/assets', {
    params: buildParams({
      asset: assetFilter.value,
      sort: sortDirection.value
    })
  })

  if (response.data.success) {
    assets.value = response.data.data.assets
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات دارایی‌ها'
    clearAssets()
  }
}, {
  onClear: clearAssets,
  logLabel: 'Assets fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

onMounted(() => {
  fetchAssets()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

