<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="اطلاعات ثبت نام"
      subtitle="مدیریت و مشاهده اطلاعات ثبت نام کاربران"
    />

    <div class="mb-6">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس نام، ایمیل یا کد شهروندی..."
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
      empty-state-message="کاربری تعریف نشده است"
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
  { key: 'id', label: 'نام کاربری' },
  { key: 'name', label: 'نام' },
  { key: 'code', label: 'کد شهروندی', defaultValue: '-' },
  { key: 'email', label: 'ایمیل' },
  { key: 'email_verified_at', label: 'تاریخ وریفای ایمیل', textSecondary: true, defaultValue: '-' },
  { key: 'ip', label: 'آی پی ثبت نام', textSecondary: true, defaultValue: '-' }
]

const clearUsers = () => {
  users.value = []
  pagination.value = null
}

const fetchRegistrationInfo = () => execute(async () => {
  const response = await apiClient.get('/registration-info', { params: buildParams() })

  if (response.data.success) {
    users.value = response.data.data.users
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات ثبت نام'
    clearUsers()
  }
}, {
  onClear: clearUsers,
  logLabel: 'Registration info fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

const handleSearch = () => search(fetchRegistrationInfo)
const handleClear = () => clear(fetchRegistrationInfo)
const onPageChange = (page) => goToPage(page, fetchRegistrationInfo)

onMounted(() => {
  fetchRegistrationInfo()
})
</script>
