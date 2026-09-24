<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="جزئیات پروفایل"
      subtitle="مشاهده جزئیات و آمار پروفایل کاربران"
    />

    <div class="mb-6">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس کد شهروندی..."
        :debounce-ms="500"
        container-class="max-w-md"
        @search="handleSearch"
        @clear="handleClear"
      />
    </div>

    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <Table
      v-else
      :columns="tableColumns"
      :data="users"
      :pagination="pagination"
      empty-state-message="کاربری یافت نشد"
    />

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
import { Table, Pagination, SearchBox, LoadingState, ErrorState, PageHeader } from '../../components/ui'
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

const users = ref([])

const tableColumns = [
  { key: 'code', label: 'شناسه شهروندی' },
  { key: 'created_at', label: 'تاریخ و ساعت ثبت نام' },
  { key: 'activities_sum_total', label: 'کل زمان حضور' },
  { key: 'followers_count', label: 'تعداد مشترکین' },
  { key: 'payments_count', label: 'خرید ابزار و PSC' },
  { key: 'more_than_a_million_payment', label: 'تعداد پرداخت های بالای ۱ میلیون تومان' },
  { key: 'total_deposit_amount', label: 'مجموع مبلغ واریزی (تومان)' },
  { key: 'score', label: 'کل امتیاز دریافتی' }
]

const clearUsers = () => {
  users.value = []
  pagination.value = null
}

const fetchProfileDetails = () => execute(async () => {
  const response = await apiClient.get('/profile-details', { params: buildParams() })

  if (response.data.success) {
    users.value = response.data.data.users
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات جزئیات پروفایل'
    clearUsers()
  }
}, {
  onClear: clearUsers,
  logLabel: 'Profile details fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

const handleSearch = () => search(fetchProfileDetails)
const handleClear = () => clear(fetchProfileDetails)
const onPageChange = (page) => goToPage(page, fetchProfileDetails)

onMounted(() => {
  fetchProfileDetails()
})
</script>
